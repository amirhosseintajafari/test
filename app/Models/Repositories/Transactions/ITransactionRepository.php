<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Entities\Transaction;
use Illuminate\Support\Collection;

interface ITransactionRepository
{
     public function create(Transaction $transaction): Transaction;
}
