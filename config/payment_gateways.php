<?php

return [
    'gateways' => [
        [
            'name' => 'zarinpal',
            'base_url' => env('APP_URL') . '/ap/mock-payment',
            'merchant_id' => 123456,
            'priority' => 1,
            'max_request' => 4,
        ],
        [
            'name' => 'payping',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => 789456,
            'priority' => 2,
            'max_request' => 3,

        ],
        [
            'name' => 'idpay',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => 985632,
            'priority' => 3,
            'max_request' => 2,

        ],
    ]
];
