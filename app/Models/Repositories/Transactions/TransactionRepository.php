<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Transaction;

class TransactionRepository implements ITransactionRepository
{

    public function __construct(private MySqlTransactionRepository $mySqlTransactionRepository){}

    public function create(Transaction $transaction): Transaction
    {
        return $this->mySqlTransactionRepository->create($transaction);
    }

    public function update(Transaction $transaction): Transaction
    {
        return $this->mySqlTransactionRepository->update($transaction);
    }

    public function lockTransactionForUpdate(Transaction $transaction): Transaction
    {
        return $this->mySqlTransactionRepository->lockTransactionForUpdate($transaction);
    }
}
