<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockPaymentController extends Controller
{
    public function handleMockPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'callback' => 'required|url',
        ]);

        $isSuccessful = rand(0, 1);

        if ($isSuccessful) {
            $responseCode = rand(1000, 9999);
            return response()->json([
                'status' => 'success',
                'message' => 'پرداخت شبیه‌سازی‌شده موفق بود.',
                'transaction_code' => $responseCode,
                'response_code' => '200',
                'redirect_url' => $request->input('callback') . '?response_code=' . $responseCode . '&status=success',
            ]);
        } else {
            $statuses = ['10', '11', '12', '13', '14', '15'];
            $responseCode = $statuses[array_rand($statuses)];

            $statusMessages = [
                '10' => 'canceled',
                '11' => 'blocked',
                '12' => 'under_review',
                '13' => 'reversed',
                '14' => 'pending',
                '15' => 'failed'
            ];

            return response()->json([
                'status' => isset($statusMessages[$responseCode]) ? $statusMessages[$responseCode] : 'failed',
                'message' => 'پرداخت شبیه‌سازی‌شده ناموفق بود.',
                'transaction_id' => null,
                'response_code' => $responseCode,
                'redirect_url' => $request->input('callback') . '?transaction_id=' . null . '&status=failed',
            ]);
        }
    }
}
