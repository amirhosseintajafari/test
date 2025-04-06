<?php

namespace App\Services;

use App\DataTransferObjects\ConvertCardNumberToShabaNumberData;
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

    public function handlePayment(int $amount, int $orderId, string $callbackUrl, int $creatorId): void
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

        $this->processPayment($amount, $callbackUrl, $transaction,'normal');
    }

    public function handlePaymentPaya(int $amount, int $orderId, string $callbackUrl, int $creatorId, string $shabaNumber): void
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

        $this->processPayment($amount, $callbackUrl, $transaction,'paya');
    }

    public function handlePaymentSatna(int $amount, int $orderId, string $callbackUrl, int $creatorId, string $shabaNumber): void
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

        $this->processPayment($amount, $callbackUrl, $transaction,'satna');
    }

    public function processPayment(int $amount, string $callbackUrl, Transaction $transaction,String $payment_type)
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
            $requestData = [
                'merchant_id' => $gateway['merchant_id'] ?? null,
                'username' => $gateway['username'] ?? null,
                'password' => $gateway['password'] ?? null,
                'amount' => $amount,
                'gateway' => $gateway,
                'callback' => $callbackUrl,
                'cacheKeyTransaction' => $cacheKey,
                'transaction' => $transaction,
                'payment_type' => $payment_type
            ];

            $this->attemptPayment($requestData);
        }

    }

    private function hasTooManyFailedAttempts(int $transactionId): bool
    {
        return Cache::get("failed_attempts_$transactionId", 0) >= 5;
    }


    private function attemptPayment(array $requestData): void
    {
        $handler = new JobHandler();
        $handler->sendToGateway($requestData);
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

    public function handleConvertCardNumberToShabaNumber($cardNumber,$callbackUrl)
    {
        foreach ($this->gateways as $gateway) {
            $requestData = [
                'merchant_id' => $gateway['merchant_id'] ?? null,
                'username' => $gateway['username'] ?? null,
                'password' => $gateway['password'] ?? null,
                'gateway' => $gateway,
                'cardNumber' => $cardNumber,
                'callback' => $callbackUrl,
            ];

            try {
                $openBankingClass = new $requestData['gateway']['class']();
                $requestData = $openBankingClass->buildRequestDataForConvertCardNumberToShabaNumber($requestData);
                $response = $openBankingClass->convertCardNumberToShabaNumber($requestData);
                $responseData = $openBankingClass->getResponseCardNumberToShabaNumber($response,$requestData);
                if ($responseData['status'] === 'success'){
                    return $responseData;
                }else{

                }
            }catch(Exception){

            }


        }
    }
}
