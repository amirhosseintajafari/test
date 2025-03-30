<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Entities\Transaction;
use Illuminate\Support\Collection;

class TransactionRepository implements ITransactionRepository
{

    public function getOneById(int $id): null|Transaction
    {
        // TODO: Implement getOneById() method.
    }

    public function getAllByIds(array $ids): Collection
    {
        // TODO: Implement getAllByIds() method.
    }

    public function create(Transaction $applicant): Transaction
    {
        // TODO: Implement create() method.
    }
}
