<?php

namespace App\Models\Repositories\Logs;

use App\Models\Entities\Log;
use Illuminate\Support\Collection;

interface ILogRepository
{
    public function getOneById(int $id): null|Log;

    public function getAllByIds(array $ids): Collection;

    public function create(Log $log): Log;
}
