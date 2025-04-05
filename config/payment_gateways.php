<?php

return [
    'gateways' => [
        [
            'name' => 'beh_pardakht',
            'base_url' => env('APP_URL') . '/apه/mock-payment',
            'merchant_id' => null,
            'priority' => 1,
            'max_request' => 4,
            'password' => 1365241,
            'username' => 'amirhossein',
            'class' => \App\Helpers\ApiClasses\Openbankings\BehPardakhtApi::class
        ],
        [
            'name' => 'shahin',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => 789456,
            'priority' => 2,
            'max_request' => 3,
            'class' => \App\Helpers\ApiClasses\Openbankings\ShahinApi::class


        ],
        [
            'name' => 'saman',
            'base_url' => env('APP_URL') . '/api/mock-payment',
            'merchant_id' => 985632,
            'priority' => 3,
            'max_request' => 2,
            'class' => \App\Helpers\ApiClasses\Openbankings\SamanApi::class

        ],
    ]
];
