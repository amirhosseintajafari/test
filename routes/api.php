<?php

use App\Http\Controllers\MockPaymentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/payment', [PaymentController::class, 'handlePayment']);
//Route::get('/payment/callback', [PaymentController::class, 'handleCallback']);
Route::post('mock-payment', [MockPaymentController::class, 'handleMockPayment']);
