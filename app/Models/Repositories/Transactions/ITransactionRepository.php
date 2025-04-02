<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Transaction;

interface ITransactionRepository
{
     public function create(Transaction $transaction): Transaction;
}
