<?php

namespace App\Models\Repositories\Logs;

interface ILogRepository
{
    public function insert(array $logs);
}
