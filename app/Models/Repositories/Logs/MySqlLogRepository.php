<?php

namespace App\Models\Repositories\Logs;

use App\Models\Entities\Log;
use Illuminate\Support\Collection;

class MySqlLogRepository implements ILogRepository
{
    public function insert(array $logs)
    {
        Log::query()->insert($logs);
    }
}
