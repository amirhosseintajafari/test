<?php

namespace App\Models\Repositories\Logs;

class LogRepository implements ILogRepository
{
    public function __construct(private MySqlLogRepository $mySqlLogRepository){}

    public function insert(array $logs)
    {
        $this->mySqlLogRepository->insert($logs);
    }
}
