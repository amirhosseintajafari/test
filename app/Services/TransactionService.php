<?php

namespace App\Services;

use App\Models\Entities\Transaction;
use App\Models\Repositories\Transactions\TransactionRepository;

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
