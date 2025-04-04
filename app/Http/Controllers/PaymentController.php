<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Enums\StatusEnum;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{


    public function __construct(private PaymentGatewayService $paymentGatewayService){}

    public function handlePayment(PaymentRequest $request)
    {
        try {
            $this->paymentGatewayService->handleUserPayment(
                amount: $request->input('amount'),
                orderId: $request->input('order_id'),
                callbackUrl: $request->input('callback_url'),
                creatorId: auth()->id() ?? 1
            );
            return response()->json([
                    'status' => 'success',
                    'message' => 'درخواست پرداخت ارسال شد.',
                ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
