<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\PaymentData;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentGatewayService;

class PaymentController extends Controller
{
    public function __construct(private PaymentGatewayService $paymentGatewayService){}

    public function handlePayment(PaymentRequest $request)
    {
        try {
            $paymentData = PaymentData::fromRequest($request);

            $this->paymentGatewayService->handleUserPayment(
                amount: $paymentData->amount,
                orderId: $paymentData->orderId,
                callbackUrl: $paymentData->callbackUrl,
                creatorId: $paymentData->creatorId
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
