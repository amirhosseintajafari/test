<?php

namespace App\Services;

use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;

class TransactionService
{

    public function __construct(private TransactionRepository $transactionRepository){}

    public function create(Transaction $transaction)
    {
        $this->transactionRepository->create($transaction);
    }

    public function update(Transaction $transaction)
    {
        $this->transactionRepository->update($transaction);
    }
}
