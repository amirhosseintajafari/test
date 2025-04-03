<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $statuses = [
                ResponseCodeEnum::CANCELED->value,
                ResponseCodeEnum::BLOCKED->value,
                ResponseCodeEnum::UNDER_REVIEW->value,
                ResponseCodeEnum::REVERSED->value,
                ResponseCodeEnum::PENDING->value,
                ResponseCodeEnum::FAILED->value
            ];

            $responseCode = $statuses[array_rand($statuses)];

            $statusMessages = [
                ResponseCodeEnum::CANCELED->value => StatusEnum::CANCELED->value,
                ResponseCodeEnum::BLOCKED->value => StatusEnum::BLOCKED->value,
                ResponseCodeEnum::UNDER_REVIEW->value => StatusEnum::UNDER_REVIEW->value,
                ResponseCodeEnum::REVERSED->value => StatusEnum::REVERSED->value,
                ResponseCodeEnum::PENDING->value => StatusEnum::PENDING->value,
                ResponseCodeEnum::FAILED->value => StatusEnum::FAILED->value
            ];

            return response()->json([
                'status' => $statusMessages[$responseCode] ?? StatusEnum::FAILED->value,
                'message' => 'پرداخت شبیه‌سازی‌شده ناموفق بود.',
                'transaction_id' => null,
                'response_code' => $responseCode,
                'redirect_url' => $request->input('callback') . '?transaction_id=' . null . '&status=' . StatusEnum::FAILED->value,
            ]);
        }
    }
}
