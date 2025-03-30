<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Entities\Transaction;
use Illuminate\Support\Collection;

interface ITransactionRepository
{
    public function getOneById(int $id): null|Transaction;

    public function getAllByIds(array $ids): Collection;

    public function create(Transaction $transaction): Transaction;
}
