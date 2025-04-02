<?php

namespace App\Http\Controllers;

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

    public function handlePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000', // حداقل ۱۰۰۰ تومان
            'callback_url' => 'required|url',
            'order_id' => 'required'
        ]);


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

            if (filled($paymentResult)) {
                $transaction->status = 'paid';
                $transaction->transaction_code = $paymentResult['transaction_code'];
                $transaction->gateway_name = $paymentResult['gateway_name'];
                $transaction->response_code = $paymentResult['response_code'];
                $this->paymentGatewayService->updateTransaction($transaction);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'درخواست پرداخت ارسال شد.',
                'response_code' => $paymentResult['response_code'],
                'payment_url' => $paymentResult['redirect_url'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

//    public function handleCallback(Request $request)
//    {
//        $transactionId = $request->query('transaction_id');
//        $gateway = $request->query('gateway');
//        $paymentStatus = $request->query('status');
//
//        if (!$transactionId || !$gateway) {
//            return response()->json(['status' => 'error', 'message' => 'اطلاعات تراکنش نامعتبر است.']);
//        }
//
//        if ($paymentStatus === 'success') {
//            return response()->json([
//                'status' => 'success',
//                'message' => 'پرداخت موفقیت‌آمیز بود.',
//                'transaction_id' => $transactionId,
//                'gateway' => $gateway,
//            ]);
//        }
//
//        return response()->json([
//            'status' => 'failed',
//            'message' => 'پرداخت انجام نشد یا ناموفق بود.',
//            'transaction_id' => $transactionId,
//            'gateway' => $gateway,
//        ]);
//    }
}
