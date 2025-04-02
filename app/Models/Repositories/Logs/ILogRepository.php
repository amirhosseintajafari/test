<?php

namespace App\Models\Repositories\Logs;

use App\Models\Entities\Log;
use Illuminate\Support\Collection;

interface ILogRepository
{
    public function insert(array $logs);
}
