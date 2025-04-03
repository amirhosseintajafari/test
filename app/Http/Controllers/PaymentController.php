<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Enums\StatusEnum;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function handlePayment(PaymentRequest $request)
    {
        $amount = $request->input('amount');
        $orderId = $request->input('order_id');
        $callbackUrl = $request->input('callback_url');

        $transaction = new Transaction();
        $transaction->amount = $amount;
        $transaction->order_id = $orderId;
        $transaction->creator_id = 1;

        $transaction = $this->paymentGatewayService->createTransaction($transaction);
        try {
            $paymentResult = $this->paymentGatewayService->processPayment($amount, $callbackUrl, $transaction);
;
            if (filled($paymentResult)) {
                $transaction->status = StatusEnum::PAID->value;
                $transaction->transaction_code = $paymentResult['transaction_code'];
                $transaction->gateway_name = $paymentResult['gateway_name'];
                $transaction->response_code = $paymentResult['response_code'];
                $this->paymentGatewayService->updateTransaction($transaction);
                return response()->json([
                    'status' => 'success',
                    'message' => 'درخواست پرداخت ارسال شد.',
                    'response_code' => $paymentResult['response_code'],
                    'payment_url' => $paymentResult['redirect_url'],
                ]);
            }else{
                return response()->json([
                    'status' => 'failed',
                    'message' => 'تعداد درخواست ها بیش از اندازه مجاز می باشد'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
