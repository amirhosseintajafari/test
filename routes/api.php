<?php

use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/payment', [PaymentController::class, 'handlePayment']);
Route::post('/payment/satna', [PaymentController::class, 'handlePaymentSatna']);
Route::post('/payment/paya', [PaymentController::class, 'handlePaymentPaya']);
Route::post('payment/convert-card-number-to-shaba-number', [PaymentController::class, 'convertCardNumberToShabaNumber']);


Route::post('mock-payment/normal', [MockPaymentController::class, 'handleMockPayment']);
Route::post('mock-payment/satna', [MockPaymentController::class, 'handleMockPaymentSatna']);
Route::post('mock-payment/paya', [MockPaymentController::class, 'handleMockPaymentPaya']);
Route::post('mock-payment/convert-card-number-to-shaba-number', [MockPaymentController::class, 'convertCardNumberToShabaNumber']);
