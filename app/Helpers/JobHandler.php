<?php

namespace App\Helpers;

use App\Jobs\SendResponse;
use App\Jobs\SendToGatewayJob;
use App\Models\Transaction;

class JobHandler
{
    public function sendToGateway(array $requestData)
    {
        $job = new SendToGatewayJob($requestData);
        dispatch($job)->onQueue('send_to_gateway');
    }

    public function sendResponse(string $callbackUrl, Transaction $transaction)
    {
        $job = new SendResponse($callbackUrl, $transaction);
        dispatch($job)->onQueue('send_response');
    }
}
