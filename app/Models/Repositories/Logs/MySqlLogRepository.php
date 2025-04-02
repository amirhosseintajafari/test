<?php

namespace App\Models\Repositories\Logs;

use App\Models\Log;

class MySqlLogRepository implements ILogRepository
{
    public function insert(array $logs)
    {
        Log::query()->insert($logs);
    }
}
