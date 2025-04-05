<?php

namespace App\Helpers\ApiClasses\Openbankings;

use App\Helpers\ApiClasses\Interfaces\IOpenbanking;
use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use Illuminate\Support\Facades\Http;

class SamanApi implements IOpenbanking
{

    public function buildRequestData($requestData): array
    {
        return [
            'merchant_id' => $requestData['merchant_id'],
            'amount' => $requestData['amount'],
            'callback' => $requestData['callback'],
            'payment_type' => $requestData['payment_type'],
        ];
    }

    public function sendToOpenBanking($gateway, $requestData)
    {
        return Http::post("{$gateway['base_url']}/" . $requestData['payment_type'], $requestData);
    }

    public function getResponseData($response, $requestData)
    {
        if ($response->successful()) {
            return [
                'status' => $response->json('status'),
                'message' => $response->json('message'),
                'transaction_code' => $response->json('transaction_code'),
                'redirect_url' => $response->json('redirect_url'),
                'response_code' => $response->json('response_code'),
            ];
        }
        return [
            'status' => StatusEnum::FAILED->value,
            'response_code' => ResponseCodeEnum::INTERNAL_ERROR->value,
            'response_data' => null,
            'request_data' => json_encode($requestData),
            'updated_at' => json_encode(now()),
            'created_at' => json_encode(now()),
        ];
    }
}
