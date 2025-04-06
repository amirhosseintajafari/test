<?php

namespace App\Http\Controllers;

use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use Illuminate\Http\Request;

class MockPaymentController extends Controller
{
    public function handleMockPayment(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'callback' => 'required|url',
        ]);

        $isSuccessful = rand(0,5);

        if ($isSuccessful == 1) {
            $responseCode = rand(1000, 9999);
            return response()->json([
                'status' => 'success',
                'message' => 'پرداخت شبیه‌سازی‌شده موفق بود.',
                'transaction_code' => $responseCode,
                'response_code' => ResponseCodeEnum::PAID->value,
                'redirect_url' => $request->input('callback') . '?response_code=' . $responseCode . '&status=success',
            ]);
        } else {
            $statusMessages = StatusEnum::getStatusMessages();

            $responseCode = array_rand($statusMessages);

            $statusMessage = $statusMessages[$responseCode];


            return response()->json([
                'status' => $statusMessage[$responseCode] ?? StatusEnum::FAILED->value,
                'message' => 'پرداخت شبیه‌سازی‌شده ناموفق بود.',
                'transaction_id' => null,
                'response_code' => $responseCode,
                'redirect_url' => $request->input('callback') . '?transaction_id=' . null . '&status=' . StatusEnum::FAILED->value,
            ]);
        }
    }

    public function handleMockPaymentPaya(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'callback' => 'required|url',
        ]);

        $isSuccessful = rand(0,5);

        if ($isSuccessful == 1) {
            $responseCode = rand(1000, 9999);
            return response()->json([
                'status' => 'success',
                'message' => 'پرداخت شبیه‌سازی‌شده موفق بود.',
                'transaction_code' => $responseCode,
                'response_code' => ResponseCodeEnum::PAID->value,
                'redirect_url' => $request->input('callback') . '?response_code=' . $responseCode . '&status=success',
            ]);
        } else {
            $statusMessages = StatusEnum::getStatusMessages();

            $responseCode = array_rand($statusMessages);

            $statusMessage = $statusMessages[$responseCode];


            return response()->json([
                'status' => $statusMessage[$responseCode] ?? StatusEnum::FAILED->value,
                'message' => 'پرداخت شبیه‌سازی‌شده ناموفق بود.',
                'transaction_id' => null,
                'response_code' => $responseCode,
                'redirect_url' => $request->input('callback') . '?transaction_id=' . null . '&status=' . StatusEnum::FAILED->value,
            ]);
        }
    }

    public function handleMockPaymentSatna(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'callback' => 'required|url',
        ]);

        $isSuccessful = rand(0,5);

        if ($isSuccessful == 1) {
            $responseCode = rand(1000, 9999);
            return response()->json([
                'status' => 'success',
                'message' => 'پرداخت شبیه‌سازی‌شده موفق بود.',
                'transaction_code' => $responseCode,
                'response_code' => ResponseCodeEnum::PAID->value,
                'redirect_url' => $request->input('callback') . '?response_code=' . $responseCode . '&status=success',
            ]);
        } else {
            $statusMessages = StatusEnum::getStatusMessages();

            $responseCode = array_rand($statusMessages);

            $statusMessage = $statusMessages[$responseCode];


            return response()->json([
                'status' => $statusMessage[$responseCode] ?? StatusEnum::FAILED->value,
                'message' => 'پرداخت شبیه‌سازی‌شده ناموفق بود.',
                'transaction_id' => null,
                'response_code' => $responseCode,
                'redirect_url' => $request->input('callback') . '?transaction_id=' . null . '&status=' . StatusEnum::FAILED->value,
            ]);
        }
    }

    public function convertCardNumberToShabaNumber(Request $request)
    {
        $request->validate([
           'cardNumber' => 'required'
        ]);
        $shabaNumber = 'IR'.$request->cardNumber.rand(100000,999999);

        return response()->json([
            'status' => 'failed',
            'shabaNumber' => $shabaNumber,
            'redirect_url' => $request->input('callback') . '?shabaNumber=' . $shabaNumber . '&status=success',
        ]);
    }
}
