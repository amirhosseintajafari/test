<?php

namespace App\Jobs;

use App\Helpers\JobHandler;
use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use App\Models\Repositories\Logs\LogRepository;
use App\Models\Repositories\Logs\MySqlLogRepository;
use App\Models\Repositories\Transactions\MySqlTransactionRepository;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Services\PaymentGatewayService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

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
    private mixed $openBankingClass;


    /**
     * Create a new job instance.
     */
    public function __construct(private $requestData)
    {
        $this->totalMaxRequest = array_sum(array_column(config('payment_gateways.gateways'), 'max_request'));
        $this->openBankingClass = new $requestData['gateway']['class']();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->updateCacheMaxRequestCount($this->requestData['cacheKeyTransaction'], $this->totalMaxRequest);
        $this->initRepositoriesAndServices();

        if ($this->shouldSkip()) {
            return;
        }

        $cacheKeyMaxRequestGateway = $this->buildCacheKey();
        try {
            $requestData = $this->openBankingClass->buildRequestData($this->requestData);
            $response = $this->sendToGateway($this->requestData['gateway'], $requestData);
            $this->updateCacheRequestCount($cacheKeyMaxRequestGateway);

            $status = StatusEnum::getPaymentStatus($response);

            if ($this->isInternalError($response)) {
                $this->markGatewayAsUnavailable($this->requestData['gateway']['name']);
            }

            $logs = $this->buildLogEntry($this->requestData['transaction']->id, $this->requestData['gateway']['name'], $status, $response, $requestData);

            $this->updateTransaction($status, $response);
            if ($this->checkCacheMaxRequestCount($this->requestData['cacheKeyTransaction'])) {
                (new JobHandler())->sendResponse($this->requestData['callbackUrl'], $this->requestData['transaction']);
                return;
            }

            if ($status === StatusEnum::PAID->value) {
                (new JobHandler())->sendResponse($this->requestData['callbackUrl'], $this->requestData['transaction']);
                $this->finalizeSuccessfulPayment($this->requestData['cacheKeyTransaction'], $this->requestData['gateway']['name'], $response, $this->buildRequestData($this->requestData['gateway'], $this->requestData['amount'], $this->requestData['callbackUrl']));
                return;
            } else {
                $this->retryIfNeeded($cacheKeyMaxRequestGateway);
            }
        } catch (Exception) {
            $logs = $this->handleFailedPayment($this->requestData['transaction']->id, $this->requestData['gateway']['name'], $this->requestData);
            $this->markGatewayAsUnavailable($this->requestData['gateway']['name']);
            Cache::increment("failed_attempts_{$this->requestData['transaction']->id}");
        }

        $this->logRepository->insert($logs ?? []);
    }

    private function sendToGateway($gateway, $requestData)
    {
        try {
            $response = $this->openBankingClass->sendToOpenBanking($gateway, $requestData);
            return $this->openBankingClass->getResponseData($response,$requestData);

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
        return $this->requestData['transaction']->status === StatusEnum::PAID->value
            || !isGatewayAvailable($this->requestData['gateway']['name']);
    }

    private function buildCacheKey(): string
    {
        return "txn:{$this->requestData['transaction']->id}:gateway:{$this->requestData['gateway']['name']}:requests";
    }

    private function updateCacheRequestCount(string $cacheKey): void
    {
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, 1, now()->addMinutes(10));
        } else {
            Cache::increment($cacheKey);
        }
    }

    private function updateCacheMaxRequestCount(string $cacheKey, int $totalMaxRequest): void
    {
        if (!Cache::has($cacheKey . 'max_request')) {
            Cache::put($cacheKey . 'max_request', $totalMaxRequest, now()->addMinute());
        } else {
            Cache::decrement($cacheKey . 'max_request');
        }
    }

    private function checkCacheMaxRequestCount(string $cacheKey): bool
    {
        return Cache::get($cacheKey . 'max_request') === 0;
    }

    private function isInternalError(array $response): bool
    {
        return $response['response_code'] === ResponseCodeEnum::INTERNAL_ERROR->value;
    }


    private function retryIfNeeded(string $cacheKey): void
    {
        if (Cache::get($cacheKey) < $this->requestData['gateway']['max_request']) {
            (new JobHandler())->sendToGateway($this->requestData);
        }
    }

    /**
     * @param string $status
     * @param array $response
     * @return void
     */
    public function updateTransaction(string $status, array $response): void
    {
        $this->requestData['transaction']->status = $status;
        $this->requestData['transaction']->transaction_code = $response['transaction_code'] ?? null;
        $this->requestData['transaction']->gateway_name = $this->requestData['gateway']['name'];
        $this->requestData['transaction']->response_code = $response['response_code'] ?? null;

        $this->paymentGatewayService->updateTransaction($this->requestData['transaction']);
    }

}
