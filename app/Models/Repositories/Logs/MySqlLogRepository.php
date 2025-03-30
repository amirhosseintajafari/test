<?php

namespace App\Models\Repositories\Logs;

use App\Models\Entities\Log;
use Illuminate\Support\Collection;

class MySqlLogRepository implements ILogRepository
{

    public function getOneById(int $id): null|Log
    {
        // TODO: Implement getOneById() method.
    }

    public function getAllByIds(array $ids): Collection
    {
        // TODO: Implement getAllByIds() method.
    }

    public function create(Log $log): Log
    {
        // TODO: Implement create() method.
    }
}
