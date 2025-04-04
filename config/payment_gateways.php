<?php

return [
    'gateways' => [
        [
            'name' => 'zarinpal',
            'base_url' => env('APP_URL') . '/apه/mock-payment',
            'merchant_id' => null,
            'priority' => 1,
            'max_request' => 4,
            'password' => 1365241,
            'username' => 'amirhossein'
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
