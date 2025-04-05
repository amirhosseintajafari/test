<?php

namespace Tests\Unit;

use App\Models\Repositories\Logs\LogRepository;
use App\Models\Repositories\Transactions\TransactionRepository;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;


class PaymentServiceTest extends TestCase
{
    protected $paymentService;
    protected $logRepository;
    protected $transactionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logRepository = Mockery::mock(LogRepository::class);
        $this->transactionRepository = Mockery::mock(TransactionRepository::class);
        $this->paymentService = new PaymentGatewayService($this->logRepository, $this->transactionRepository);

        Cache::flush();
    }

//    public function testSuccessfulPayment()
//    {
//        $transaction = Transaction::factory()->create();
////        $gateway = ['name' => 'zarinpal'];
////
//        $this->paymentService->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
//
//        $this->paymentService = Mockery::mock(PaymentGatewayService::class)->makePartial();
//        $this->paymentService->shouldReceive('isGatewayAvailable')->andReturn(true);
//        $this->paymentService->shouldReceive('sendToGateway')->andReturn([
//            'response_code' => 200,
//            'transaction_code' => 'TX123',
//            'redirect_url' => 'https://example.com/success'
//        ]);
//
//        $this->logRepository->shouldReceive('insert')->once();
//
//        $result = $this->paymentService->processPayment(1000, 'https://callback.url', $transaction);
//
//        $this->assertEquals('paid', $result['status']);
//        $this->assertEquals('TX123', $result['transaction_code']);
//        $this->assertEquals('https://example.com/success', $result['redirect_url']);
//    }
//
//    public function testFailedPayment()
//    {
//        $transaction = Transaction::factory()->create();
////        $gateway = ['name' => 'zarinpal'];
////
//        $this->paymentService->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
//
//
//        $this->paymentService = Mockery::mock(PaymentGatewayService::class)->makePartial();
//        $this->paymentService->shouldReceive('isGatewayAvailable')->andReturn(true);
//        $this->paymentService->shouldReceive('sendToGateway')->andReturn([
//            'response_code' => 15
//        ]);
//
//        $this->logRepository->shouldReceive('insert')->once();
//
//        $result = $this->paymentService->processPayment(1000, 'https://callback.url', $transaction);
//
//        $this->assertEquals('failed', $result['status']);
//    }
//
//    public function testGatewayUnavailable()
//    {
//        $transaction = Transaction::factory()->create();
//        $this->paymentService->gateways = collect(config('payment_gateways.gateways'))->sortBy('priority');
//
//
//        $this->paymentService = Mockery::mock(PaymentGatewayService::class)->makePartial();
//        $this->paymentService->shouldReceive('isGatewayAvailable')->andReturn(false);
//
//        $result = $this->paymentService->processPayment(1000, 'https://callback.url', $transaction);
//
//        $this->assertEquals('failed', $result['status']);
//    }

//    public function testMaxFailedAttempts()
//    {
//        $transaction = Transaction::factory()->create();
//        Cache::put("failed_attempts_{$transaction->id}", 5);
//
//        $this->expectException(\Exception::class);
//        $this->expectExceptionMessage("تعداد تلاش‌های ناموفق زیاد است. لطفاً بعداً تلاش کنید.");
//
//        $this->paymentService->processPayment(1000, 'https://callback.url', $transaction);
//    }
}
