<?php

namespace App\Models\Repositories\Logs;

use App\Models\Entities\Log;
use Illuminate\Support\Collection;

class LogRepository implements ILogRepository
{
    private $mySqlLogRepository;

    public function __construct(MySqlLogRepository $mySqlLogRepository)
    {
        $this->mySqlLogRepository = $mySqlLogRepository;
    }

    public function insert(array $logs)
    {
        $this->mySqlLogRepository->insert($logs);
    }
}
