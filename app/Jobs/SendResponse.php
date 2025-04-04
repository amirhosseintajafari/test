<?php

namespace App\Jobs;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendResponse implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $callbackUrl,private Transaction $transaction)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = [
          'status' => $this->transaction->status,
          'amount' => $this->transaction->amount,
          'response_code' => $this->transaction->response_code,
          'order_id' => $this->transaction->order_id,
          'description' => $this->transaction->description,
        ];
        Http::post($this->callbackUrl, $response);
    }
}
