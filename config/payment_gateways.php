<?php

return [
    'gateways' => [
        [
            'name' => 'zarinpal',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'priority' => 1,
        ],
        [
            'name' => 'payping',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => env('PAYPING_MERCHANT_ID'),
            'priority' => 2,
        ],
        [
            'name' => 'idpay',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => env('IDPAY_MERCHANT_ID'),
            'priority' => 3,
        ],
    ]
];
