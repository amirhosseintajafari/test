<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Transaction;

class TransactionRepository implements ITransactionRepository
{
    private MySqlTransactionRepository $mySqlTransactionRepository;

    public function __construct(MySqlTransactionRepository $mySqlTransactionRepository)
    {
        $this->mySqlTransactionRepository = $mySqlTransactionRepository;
    }

    public function create(Transaction $transaction): Transaction
    {
        return $this->mySqlTransactionRepository->create($transaction);
    }

    public function update(Transaction $transaction): Transaction
    {
        return $this->mySqlTransactionRepository->update($transaction);
    }
}
