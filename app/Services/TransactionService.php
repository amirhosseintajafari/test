<?php

namespace App\Services;

use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;

class TransactionService
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function create(Transaction $transaction)
    {
        $this->transactionRepository->create($transaction);
    }

    public function update(Transaction $transaction)
    {
        $this->transactionRepository->update($transaction);
    }
}
