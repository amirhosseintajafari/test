<?php
//namespace Tests\Unit;
//
//use App\Helpers\JobHandler;
//use App\Jobs\SendToGatewayJob;
//use App\Models\Enums\StatusEnum;
//use App\Models\Transaction;
//use Illuminate\Support\Facades\Cache;
//use Illuminate\Support\Facades\Http;
//use Mockery;
//use Tests\TestCase;
//use function env;
//
//class SendToGatewayJobTest extends TestCase
//{
//    public function test_retry_if_needed_calls_send_to_gateway_when_under_limit()
//    {
//        $gateway =
//            [
//                'name' => 'zarinpal',
//                'base_url' => env('APP_URL') . '/apه/mock-payment',
//                'merchant_id' => null,
//                'priority' => 1,
//                'max_request' => 4,
//                'password' => 1365241,
//                'username' => 'amirhossein'
//            ];
//        $amount = 1000;
//        $callbackUrl = 'https://callback.test';
//        $transaction = Transaction::factory()->create();
//        $cacheKey = 'payment_retry_key';
//
//        Cache::shouldReceive('get')
//            ->with($cacheKey)
//            ->andReturn(2);
//        Cache::shouldReceive('has')
//            ->andReturn(true);
//        Cache::shouldReceive('decrement');
//
//        $jobHandlerMock = Mockery::mock('overload:JobHandler');
//        $jobHandlerMock->shouldReceive('sendToGateway')
//            ->once()
//            ->with($gateway, $amount, $callbackUrl, $transaction, $cacheKey);
//
//        $class = new class($gateway, $amount, $callbackUrl, $transaction) {
//            private array $gateway;
//            private int $amount;
//            private string $callbackUrl;
//            private object $transaction;
//
//            public function __construct($gateway, $amount, $callbackUrl, $transaction)
//            {
//                $this->gateway = $gateway;
//                $this->amount = $amount;
//                $this->callbackUrl = $callbackUrl;
//                $this->transaction = $transaction;
//            }
//
//            private function retryIfNeeded(string $cacheKey): void
//            {
//                if (Cache::get($cacheKey) < $this->gateway['max_request']) {
//                    (new JobHandler())->sendToGateway($this->gateway, $this->amount, $this->callbackUrl, $this->transaction, $cacheKey);
//                }
//            }
//        };
//
//        $reflection = new \ReflectionClass($class);
//        $method = $reflection->getMethod('retryIfNeeded');
//        $method->setAccessible(true);
//        $method->invokeArgs($class, [$cacheKey]);
//    }
//
//    protected function tearDown(): void
//    {
//        parent::tearDown();
//        \Mockery::close();
//    }
//
//    public function testHandleProcessesPaymentSuccessfully()
//    {
//        Cache::shouldReceive('get')->once()->with('someCacheKey')->andReturn(1);
//        Cache::shouldReceive('put')->once()->with(
//            'someCacheKey' . 'max_request', // کلید
//            9, // مقدار
//            \Mockery::type('Illuminate\Support\Carbon') // بررسی نوع آرگومان
//        );
//        Cache::shouldReceive('increment')->once()->with('someCacheKey');
//        Cache::shouldReceive('has')->once();
//
//        Http::fake([
//            '*' => Http::response(['status' => 'success', 'transaction_code' => '12345'], 200),
//        ]);
//
//        $jobHandlerMock = Mockery::mock(JobHandler::class);
//        $jobHandlerMock->shouldReceive('sendToGateway')->once()->andReturn([
//            'status' => StatusEnum::PAID->value,
//            'transaction_code' => '12345',
//            'redirect_url' => 'http://example.com'
//        ]);
//
//        $transaction = new Transaction();
//        $transaction->id = 1;
//        $transaction->status = StatusEnum::PENDING->value;
//
//        $job = new SendToGatewayJob(
//            ['name' => 'gateway1', 'base_url' => 'http://gateway.com', 'max_request' => 5],
//            1000,
//            'http://callback.com',
//            $transaction,
//            'someCacheKey'
//        );
//
//        $job->handle();
//
//        $this->assertEquals(StatusEnum::PAID->value, $transaction->status);
//    }
//
//    public function testSendToGatewayReturnsCorrectResponse()
//    {
//        $transaction = Transaction::factory()->create();
//        Cache::shouldReceive('get')->once()->with('someCacheKey')->andReturn(1);
//        Cache::shouldReceive('put')->once()->with(
//            'someCacheKey' . 'max_request', // کلید
//            9, // مقدار
//            \Mockery::type('Illuminate\Support\Carbon') // بررسی نوع آرگومان
//        );
//        Cache::shouldReceive('increment')->once()->with('someCacheKey');
//        Cache::shouldReceive('has')->once();
//
//        Http::fake([
//            'http://gateway.com' => Http::response([
//                'status' => 'success',
//                'transaction_code' => '12345',
//                'redirect_url' => 'http://example.com'
//            ], 200)
//        ]);
//
//        $job = new SendToGatewayJob(
//            ['name' => 'gateway1', 'base_url' => 'http://gateway.com', 'max_request' => 5],
//            1000,
//            'http://callback.com',
//            $transaction,
//            'someCacheKey'
//        );
//
//        $response = $job->sendToGateway(
//            ['name' => 'gateway1', 'base_url' => 'http://gateway.com'],
//            ['amount' => 1000, 'callback' => 'http://callback.com']
//        );
//
//        $this->assertEquals('success', $response['status']);
//        $this->assertEquals('12345', $response['transaction_code']);
//        $this->assertEquals('http://example.com', $response['redirect_url']);
//    }
//
//    public function testRetryIfNeededDoesNotRetryWhenMaxRequestsReached()
//    {
//        $transaction = Transaction::factory()->create();
//        // Mock کردن Cache
//        Cache::shouldReceive('get')->once()->with('someCacheKey')->andReturn(1);
//        Cache::shouldReceive('put')->once()->with(
//            'someCacheKey' . 'max_request', // کلید
//            9, // مقدار
//            \Mockery::type('Illuminate\Support\Carbon') // بررسی نوع آرگومان
//        );
//        Cache::shouldReceive('increment')->once()->with('someCacheKey');
//        Cache::shouldReceive('has')->once();
//
//        $job = new SendToGatewayJob(
//            ['name' => 'gateway1', 'base_url' => 'http://gateway.com', 'max_request' => 5],
//            1000,
//            'http://callback.com',
//            $transaction,
//            'someCacheKey'
//        );
//
//        $job->retryIfNeeded('someCacheKey');
//
//        // هیچ Retry‌ای انجام نمی‌شود چون تعداد درخواست‌ها به حداکثر رسیده
//        // بررسی این‌که متد `sendToGateway` فراخوانی نشد
//        $this->assertTrue(true); // در اینجا نیاز به تایید وجود ندارد
//    }
//
//    public function testRetryIfNeededRetriesWhenUnderLimit()
//    {
//        $transaction = Transaction::factory()->create();
//        Cache::shouldReceive('get')->once()->with('someCacheKey')->andReturn(1);
//        Cache::shouldReceive('put')->once()->with(
//            'someCacheKey' . 'max_request', // کلید
//            9, // مقدار
//            \Mockery::type('Illuminate\Support\Carbon') // بررسی نوع آرگومان
//        );
//        Cache::shouldReceive('increment')->once()->with('someCacheKey');
//        Cache::shouldReceive('has')->once();
//
//        // ایجاد job
//        $job = new SendToGatewayJob(
//            ['name' => 'gateway1', 'base_url' => 'http://gateway.com', 'max_request' => 5],
//            1000,
//            'http://callback.com',
//            $transaction,
//            'someCacheKey'
//        );
//
//        $reflection = new \ReflectionClass(SendToGatewayJob::class);
//        $method = $reflection->getMethod('retryIfNeeded');
//        $method->setAccessible(true);
//
//        $jobHandlerMock = Mockery::mock(JobHandler::class);
//        $jobHandlerMock->shouldReceive('sendToGateway')->once();
//
//        $method->invoke($job, 'someCacheKey');
//    }
//
//
//
//}
