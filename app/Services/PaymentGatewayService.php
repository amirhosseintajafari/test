<?php

namespace App\Services;

use App\Models\Repositories\Logs\LogRepository;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    public $gateways;
    private LogRepository $logRepository;
    private TransactionRepository $transactionRepository;

    public function __construct(LogRepository $logRepository,TransactionRepository $transactionRepository)
    {
        $this->logRepository = $logRepository;
        $this->transactionRepository = $transactionRepository;
        $this->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
    }

    public function processPayment(int $amount, string $callbackUrl, Transaction $transaction)
    {
        $cacheKey = "payment_status_{$transaction->id}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $statusMessages = [
            '10'  => 'canceled',
            '11'  => 'blocked',
            '12'  => 'under_review',
            '13'  => 'reversed',
            '14'  => 'pending',
            '15'  => 'failed'
        ];

        $logs = [];
        $maxRequest = 10;
        $request = 0;

        $failedAttemptsKey = "failed_attempts_{$transaction->id}";
        $failedAttempts = Cache::get($failedAttemptsKey, 0);

        if ($failedAttempts >= 5) {
            throw new Exception("تعداد تلاش‌های ناموفق زیاد است. لطفاً بعداً تلاش کنید.");
        }

        while ($maxRequest >= $request ){
            foreach ($this->gateways as $gateway) {
                if (!$this->isGatewayAvailable($gateway['name'])) {
                    continue;
                }

                try {
                    $response = $this->sendToGateway($gateway, $amount, $callbackUrl);
                    Cache::forget($failedAttemptsKey);
                    $responseCode = $response['response_code'] ?? null;
                    $status = $response['response_code'] == 200 ? 'paid' : ($statusMessages[$responseCode] ?? 'failed');

                    $logs[] = [
                        'gateway_name' => $gateway['name'],
                        'transaction_id' => $transaction->id,
                        'status' => $status,
                        'response_code' => $responseCode,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                    $this->logRepository->insert($logs);

                    if ($status == 'paid'){
                        $result = [
                            'status' => $status,
                            'transaction_code' => $response['transaction_code'],
                            'redirect_url' => $response['redirect_url'] ?? null,
                            'gateway_name' => $gateway['name'],
                            'response_code' => $responseCode,
                            'message' => $status === 'paid' ? 'پرداخت موفق بود' : 'پرداخت ناموفق بود'
                        ];
                        Cache::put($cacheKey, $result, now()->addMinutes(10));
                        return $result;
                    }
                } catch (Exception $e) {
                    $logs[] = [
                        'gateway_name' => $gateway['name'],
                        'transaction_id' => $transaction->id,
                        'status' => 'failed',
                        'response_code' => 17,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                    $this->markGatewayAsUnavailable($gateway['name']);
                    Cache::increment($failedAttemptsKey);
                    continue;
                }
                $request++;
            }
        }
    }


    public function sendToGateway($gateway, float $amount, string $callbackUrl)
    {
        $requestData = [
            'merchant_id' => $gateway['merchant_id'],
            'amount' => $amount,
            'callback' => $callbackUrl,
        ];
        $response = Http::post("{$gateway['base_url']}", $requestData);

        if ($response->successful()) {
            return [
                'status' =>  $response->json('status'),
                'message' =>  $response->json('message'),
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
