<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\ConvertCardNumberToShabaNumberData;
use App\DataTransferObjects\PaymentData;
use App\DataTransferObjects\PaymentWithSabaData;
use App\Http\Requests\ConvertCardNumberToShabaNumberRequest;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentGatewayService;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $paymentGatewayService)
    {
    }

    public function handlePayment(PaymentRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $paymentData = PaymentData::fromRequest($request);

            $this->paymentGatewayService->handlePayment(
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

    public function handlePaymentSatna(PaymentRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $paymentData = PaymentWithSabaData::fromRequest($request);

            $this->paymentGatewayService->handlePaymentSatna(
                amount: $paymentData->amount,
                orderId: $paymentData->orderId,
                callbackUrl: $paymentData->callbackUrl,
                creatorId: $paymentData->creatorId,
            );

            return response()->json([
                'status' => 'success',
                'message' => 'درخواست پرداخت ارسال شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function handlePaymentPaya(PaymentRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $paymentData = PaymentWithSabaData::fromRequest($request);

            $this->paymentGatewayService->handlePaymentPaya(
                amount: $paymentData->amount,
                orderId: $paymentData->orderId,
                callbackUrl: $paymentData->callbackUrl,
                creatorId: $paymentData->creatorId,
            );

            return response()->json([
                'status' => 'success',
                'message' => 'درخواست پرداخت ارسال شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function ConvertCardNumberToShabaNumber(ConvertCardNumberToShabaNumberRequest $request)
    {
        try {
            $convertData = ConvertCardNumberToShabaNumberData::fromRequest($request);

            $response = $this->paymentGatewayService->handleConvertCardNumberToShabaNumber(
                cardNumber: $convertData->cardNumber,
                callbackUrl: $convertData->callbackUrl,
            );
            if (filled($response) && $response['status'] == 'success') {
                return response()->json([
                    'status' => 'success',
                    'shabaNumber' => $response['shabaNumber'],
                    'message' => '',
                ]);
            }else{
                return response()->json([
                    'status' => 'failed',
                    'message' => '',
                ]);
            }
        }  catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
