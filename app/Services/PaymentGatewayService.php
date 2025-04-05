<?php

namespace App\Services;

use App\Helpers\JobHandler;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Cache;


class PaymentGatewayService
{
    public \Illuminate\Support\Collection $gateways;


    public function __construct(private readonly TransactionRepository $transactionRepository)
    {
        $this->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
    }

    public function handleUserPayment(int $amount, int $orderId, string $callbackUrl, int $creatorId): void
    {
        if (filled($this->gateways) === false) {
            throw new Exception("تمام درگاه ها به مشکلی خورده است.");
        }

        $transaction = new Transaction();
        $transaction->amount = $amount;
        $transaction->order_id = $orderId;
        $transaction->creator_id = $creatorId;

        $transaction = $this->createTransaction($transaction);
        $transaction = $this->lockTransactionForUpdate($transaction);

        $this->processPayment($amount, $callbackUrl, $transaction);
    }

    public function processPayment(int $amount, string $callbackUrl, Transaction $transaction)
    {

        $cacheKey = "payment_status_$transaction->id";
        if ($cachedResponse = Cache::get($cacheKey)) {
            return $cachedResponse;
        }

        if ($this->hasTooManyFailedAttempts($transaction->id)) {
            throw new Exception("تعداد تلاش‌های ناموفق زیاد است. لطفاً بعداً تلاش کنید.");
        }

        foreach ($this->gateways as $gateway) {
            if (isGatewayAvailable($gateway['name']) == false) {
                continue;
            }
            $this->attemptPayment($gateway, $amount, $callbackUrl, $transaction, $cacheKey);
        }

    }

    private function hasTooManyFailedAttempts(int $transactionId): bool
    {
        return Cache::get("failed_attempts_$transactionId", 0) >= 5;
    }


    private function attemptPayment(array $gateway, int $amount, string $callbackUrl, Transaction $transaction, string $cacheKey): void
    {
        $handler = new JobHandler();
        $handler->sendToGateway($gateway, $amount, $callbackUrl, $transaction, $cacheKey);
    }

    public function createTransaction(Transaction $transaction): Transaction
    {
        return $this->transactionRepository->create($transaction);
    }

    public function lockTransactionForUpdate(Transaction $transaction): Transaction
    {
        return $this->transactionRepository->lockTransactionForUpdate($transaction);
    }

    public function updateTransaction(Transaction $transaction): Transaction
    {
        return $this->transactionRepository->update($transaction);
    }
}
