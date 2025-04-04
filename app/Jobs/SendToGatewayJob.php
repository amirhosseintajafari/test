<?php

namespace App\Jobs;

use App\Helpers\JobHandler;
use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use App\Models\Repositories\Logs\LogRepository;
use App\Models\Repositories\Logs\MySqlLogRepository;
use App\Models\Repositories\Transactions\MySqlTransactionRepository;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SendToGatewayJob implements ShouldQueue
{
    use Queueable;

    private MySqlLogRepository $mysqlLogRepository;
    private LogRepository $logRepository;
    private MySqlTransactionRepository $mysqlTransactionRepository;
    private TransactionRepository $transactionRepository;
    private PaymentGatewayService $paymentGatewayService;
    private \Illuminate\Support\Collection $gateways;
    private int $totalMaxRequest;


    /**
     * Create a new job instance.
     */
    public function __construct(private $gateway,private int $amount,private string $callbackUrl,private Transaction $transaction,private string $cacheKey){
        $this->totalMaxRequest = array_sum(array_column(config('payment_gateways.gateways'), 'max_request'));
        $this->updateCacheMaxRequestCount($cacheKey,$this->totalMaxRequest);

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initRepositoriesAndServices();

        if ($this->shouldSkip()) {
            return;
        }

        $cacheKey = $this->buildCacheKey();

        try {
            $requestData = $this->buildRequestData($this->gateway, $this->amount, $this->callbackUrl);
            $response = $this->sendToGateway($this->gateway, $requestData);
            $this->updateCacheRequestCount($cacheKey);

            $status = StatusEnum::getPaymentStatus($response);

            if ($this->isInternalError($response)) {
                $this->markGatewayAsUnavailable($this->gateway['name']);
            }

            $logs = $this->buildLogEntry($this->transaction->id, $this->gateway['name'], $status, $response, $requestData);

            $this->updateTransaction($status, $response);
            if ($this->checkCacheMaxRequestCount($this->cacheKey,$this->totalMaxRequest) || $status === StatusEnum::PAID->value){
                (new JobHandler())->sendResponse( $this->callbackUrl, $this->transaction);
            }
            if ($status === StatusEnum::PAID->value) {
                $this->finalizeSuccessfulPayment($this->cacheKey, $this->gateway['name'], $response, $this->buildRequestData($this->gateway, $this->amount, $this->callbackUrl));
            } else {
                $this->retryIfNeeded($cacheKey);
            }
        } catch (Exception) {
            $logs = $this->handleFailedPayment($this->transaction->id, $this->gateway['name'], $requestData);
            $this->markGatewayAsUnavailable($this->gateway['name']);
            Cache::increment("failed_attempts_{$this->transaction->id}");
        }

        $this->logRepository->insert($logs ?? []);
    }

    private function sendToGateway($gateway, $requestData)
    {
        try {
            $response = Http::post("{$gateway['base_url']}", $requestData);
            if ($response->successful()) {
                return [
                    'status' => $response->json('status'),
                    'message' => $response->json('message'),
                    'transaction_code' => $response->json('transaction_code'),
                    'redirect_url' => $response->json('redirect_url'),
                    'response_code' => $response->json('response_code'),
                ];
            }
            return [
                'status' => StatusEnum::FAILED->value,
                'response_code' => ResponseCodeEnum::INTERNAL_ERROR->value,
                'response_data' => null,
                'request_data' => json_encode($requestData),
                'updated_at' => json_encode(now()),
                'created_at' => json_encode(now()),
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'خطا در اتصال به درگاه: ' . $e->getMessage()
            ];
        }
    }

    private function buildRequestData(array $gateway, int $amount, string $callbackUrl): array
    {
        return [
            'merchant_id' => $gateway['merchant_id'] ?? null,
            'base_url' => $gateway['base_url'] ?? null,
            'max_request' => $gateway['max_request'] ?? null,
            'password' => $gateway['password'] ?? null,
            'username' => $gateway['username'] ?? null,
            'amount' => $amount,
            'callback' => $callbackUrl,
        ];
    }

    private function buildLogEntry(int $transactionId, string $gatewayName, string $status, array $response, array $requestData): array
    {
        return [
            'transaction_id' => $transactionId,
            'status' => $status,
            'gateway_name' => $gatewayName,
            'response_code' => $response['response_code'] ?? null,
            'response_data' => json_encode($response),
            'request_data' => json_encode($requestData),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    private function finalizeSuccessfulPayment(string $cacheKey, string $gatewayName, array $response, array $requestData): void
    {
        $result = [
            'status' => StatusEnum::PAID->value,
            'transaction_code' => $response['transaction_code'],
            'redirect_url' => $response['redirect_url'] ?? null,
            'gateway_name' => $gatewayName,
            'response_code' => $response['response_code'],
            'request_data' => $requestData,
            'message' => 'پرداخت موفق بود'
        ];

        Cache::put($cacheKey, $result, now()->addMinutes(10));
    }

    private function handleFailedPayment(int $transactionId, string $gatewayName, array $requestData): array
    {
        return [
            'transaction_id' => $transactionId,
            'status' => StatusEnum::FAILED->value,
            'gateway_name' => $gatewayName,
            'response_code' => ResponseCodeEnum::INTERNAL_ERROR,
            'response_data' => null,
            'request_data' => json_encode($requestData),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    private function markGatewayAsUnavailable($gatewayName)
    {
        Cache::put("gateway_unavailable_$gatewayName", true, now()->addMinutes(10));
    }

    private function initRepositoriesAndServices(): void
    {
        $this->mysqlLogRepository = new MySqlLogRepository();
        $this->logRepository = new LogRepository($this->mysqlLogRepository);

        $this->mysqlTransactionRepository = new MySqlTransactionRepository();
        $this->transactionRepository = new TransactionRepository($this->mysqlTransactionRepository);

        $this->paymentGatewayService = new PaymentGatewayService($this->transactionRepository);
    }

    private function shouldSkip(): bool
    {
        return $this->transaction->status === StatusEnum::PAID->value
            || !isGatewayAvailable($this->gateway['name']);
    }

    private function buildCacheKey(): string
    {
        return "txn:{$this->transaction->id}:gateway:{$this->gateway['name']}:requests";
    }

    private function updateCacheRequestCount(string $cacheKey): void
    {
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, now()->addMinutes(10));
        } else {
            Cache::increment($cacheKey);
        }
    }

    private function updateCacheMaxRequestCount(string $cacheKey,int $totalMaxRequest): void
    {
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey . 'max_request', $totalMaxRequest, now()->addMinute(1));
        } else {
            Cache::decrement($cacheKey . 'max_request');
        }
    }

    private function checkCacheMaxRequestCount(string $cacheKey, int $totalMaxRequest): bool
    {
        return Cache::get($cacheKey . 'max_request') == 0;
    }

    private function isInternalError(array $response): bool
    {
        return $response['response_code'] === ResponseCodeEnum::INTERNAL_ERROR->value;
    }


    private function retryIfNeeded(string $cacheKey): void
    {
        if (Cache::get($cacheKey) < $this->gateway['max_request']) {
            (new JobHandler())->sendToGateway($this->gateway, $this->amount, $this->callbackUrl, $this->transaction, $cacheKey);
        }
    }

    /**
     * @param string $status
     * @param array $response
     * @return void
     */
    public function updateTransaction(string $status, array $response): void
    {
        $this->transaction->status = $status;
        $this->transaction->transaction_code = $response['transaction_code'] ?? null;
        $this->transaction->gateway_name = $this->gateway['name'];
        $this->transaction->response_code = $response['response_code'] ?? null;

        $this->paymentGatewayService->updateTransaction($this->transaction);
    }

}
