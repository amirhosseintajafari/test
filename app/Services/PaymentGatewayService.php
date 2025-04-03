<?php

namespace App\Services;

use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use App\Models\Repositories\Logs\LogRepository;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;
use Dflydev\DotAccessData\Data;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public $gateways;
    private LogRepository $logRepository;
    private TransactionRepository $transactionRepository;

    public function __construct(LogRepository $logRepository, TransactionRepository $transactionRepository)
    {
        $this->logRepository = $logRepository;
        $this->transactionRepository = $transactionRepository;
        $this->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
    }

    public function processPayment(int $amount, string $callbackUrl, Transaction $transaction)
    {
        $cacheKey = "payment_status_{$transaction->id}";
        if ($cachedResponse = Cache::get($cacheKey)) {
            return $cachedResponse;
        }

        if ($this->hasTooManyFailedAttempts($transaction->id)) {
            throw new Exception("تعداد تلاش‌های ناموفق زیاد است. لطفاً بعداً تلاش کنید.");
        }

        $logs = [];
        $requestCount = 0;
        $maxRequests = 15;

        while ($requestCount < $maxRequests) {
            foreach ($this->gateways as $gateway) {
                if (!$this->isGatewayAvailable($gateway['name'])) {
                    continue;
                }

                $logs = array_merge($logs, $this->attemptPayment($gateway, $amount, $callbackUrl, $transaction, $cacheKey, $requestCount));

                if ($this->isPaymentSuccessful($logs)) {
                    $this->logRepository->insert($logs);
                    return Cache::get($cacheKey);
                }
            }
        }

        $this->logRepository->insert($logs);
    }

    private function hasTooManyFailedAttempts(int $transactionId): bool
    {
        return Cache::get("failed_attempts_{$transactionId}", 0) >= 5;
    }


    private function attemptPayment(array $gateway, int $amount, string $callbackUrl, Transaction $transaction, string $cacheKey, int &$requestCount): array
    {
        $logs = [];


        for ($i = 0; $i < $gateway['max_request']; $i++) {
            try {
                $requestData = $this->buildRequestData($gateway, $amount, $callbackUrl);
                $response = $this->sendToGateway($gateway, $requestData);

                $status = $this->getPaymentStatus($response);
                $logs[] = $this->buildLogEntry($transaction->id, $gateway['name'], $status, $response, $requestData);

                if ($status == StatusEnum::PAID->value) {
                    $this->finalizeSuccessfulPayment($transaction->id, $cacheKey, $gateway['name'], $response, $requestData);
                    return $logs;
                }
            } catch (Exception $e) {
                $logs[] = $this->handleFailedPayment($transaction->id, $gateway['name'], $requestData);
                $this->markGatewayAsUnavailable($gateway['name']);
                Cache::increment("failed_attempts_{$transaction->id}");
            }

            $requestCount++;
        }

        return $logs;
    }

    private function buildRequestData(array $gateway, int $amount, string $callbackUrl): array
    {
        return [
            'merchant_id' => $gateway['merchant_id'],
            'max_request' => $gateway['max_request'],
            'amount' => $amount,
            'callback' => $callbackUrl,
        ];
    }

    private function getPaymentStatus(array $response): string
    {
        $statusMessages = [
            ResponseCodeEnum::CANCELED->value => StatusEnum::CANCELED->value,
            ResponseCodeEnum::BLOCKED->value => StatusEnum::BLOCKED->value,
            ResponseCodeEnum::UNDER_REVIEW->value => StatusEnum::UNDER_REVIEW->value,
            ResponseCodeEnum::REVERSED->value => StatusEnum::REVERSED->value,
            ResponseCodeEnum::PENDING->value => StatusEnum::PENDING->value,
            ResponseCodeEnum::FAILED->value => StatusEnum::FAILED->value
        ];

        $responseCode = $response['response_code'] ?? null;
        return $responseCode == 200 ? StatusEnum::PAID->value : ($statusMessages[$responseCode] ?? StatusEnum::FAILED->value);
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

    private function finalizeSuccessfulPayment(int $transactionId, string $cacheKey, string $gatewayName, array $response, array $requestData): void
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
        $this->logRepository->insert([$this->buildLogEntry($transactionId, $gatewayName, StatusEnum::PAID->value, $response, $requestData)]);
    }

    private function handleFailedPayment(int $transactionId, string $gatewayName, array $requestData): array
    {
        return [
            'transaction_id' => $transactionId,
            'status' => StatusEnum::FAILED,
            'gateway_name' => $gatewayName,
            'response_code' => 17,
            'response_data' => null,
            'request_data' => json_encode($requestData),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }


    private function isPaymentSuccessful(array $logs): bool
    {
        foreach ($logs as $log) {
            if ($log['status'] == StatusEnum::PAID->value) {
                return true;
            }
        }
        return false;
    }

    public function sendToGateway($gateway, $requestData)
    {

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

        throw new Exception("مشکلی در درگاه {$gateway['name']} رخ داده است.");
    }

    public function createTransaction(Transaction $transaction)
    {
        return $this->transactionRepository->create($transaction);
    }

    public function updateTransaction(Transaction $transaction)
    {
        return $this->transactionRepository->update($transaction);
    }

    public function isGatewayAvailable($gatewayName)
    {
        return !Cache::has("gateway_unavailable_{$gatewayName}");
    }

    public function markGatewayAsUnavailable($gatewayName)
    {
        Cache::put("gateway_unavailable_{$gatewayName}", true, now()->addMinutes(10));
    }
}
