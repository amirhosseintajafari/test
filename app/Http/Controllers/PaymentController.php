<?php

namespace App\Http\Controllers;

use App\Models\Entities\Log;
use App\Services\TransactionService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private Log $logService;
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService, Log $logService)
    {
        $this->logService = $logService;
        $this->transactionService = $transactionService;
    }

    public function getPaymentServiceToken()
    {
//        return
//            Cache::remember('payment_service_token', 3600, function () {
            $response = Http::withOptions([
                'verify' => false, // غیرفعال کردن بررسی SSL
            ])->asForm()->post('http://test.local/oauth/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => env('PAYMENT_CLIENT_ID'),
                'client_secret' => env('PAYMENT_CLIENT_SECRET'),
                'scope'         => '',
            ]);

            return $response->json()['access_token'];
//        });
    }

    public function payment(Request $request)
    {
dd($request->all());
    }


}
