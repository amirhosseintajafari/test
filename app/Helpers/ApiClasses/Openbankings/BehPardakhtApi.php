<?php

namespace App\Helpers\ApiClasses\Openbankings;

use App\Helpers\ApiClasses\Interfaces\IOpenbanking;
use App\Models\Enums\ResponseCodeEnum;
use App\Models\Enums\StatusEnum;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BehPardakhtApi implements IOpenbanking
{

    public function buildRequestData($requestData): array
    {
        if ($requestData['payment_type'] === 'paya') {
            return [
                'merchant_id' => $requestData['merchant_id'],
                'amount' => $requestData['amount'],
                'callback' => $requestData['callback'],
                'payment_type' => $requestData['payment_type'],
            ];
        } elseif ($requestData['payment_type'] === 'satna') {
            return [
                'merchant_id' => $requestData['merchant_id'],
                'amount' => $requestData['amount'],
                'callback' => $requestData['callback'],
                'payment_type' => $requestData['payment_type'],
            ];
        } else
            return [
                'merchant_id' => $requestData['merchant_id'],
                'amount' => $requestData['amount'],
                'callback' => $requestData['callback'],
                'payment_type' => $requestData['payment_type'],
            ];
    }

    public function sendToOpenBanking($gateway, $requestData): Response
    {
        if ($requestData['payment_type'] === 'paya') {
            return Http::post("{$gateway['base_url']}/" . $requestData['payment_type'], $requestData);
        } elseif ($requestData['payment_type'] === 'satna') {
            return Http::post("{$gateway['base_url']}/" . $requestData['payment_type'], $requestData);
        } else
            return Http::post("{$gateway['base_url']}/" . $requestData['payment_type'], $requestData);
    }

    public function getResponseData($response, $requestData): array
    {
        if ($requestData['payment_type'] === 'paya') {
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
        } elseif ($requestData['payment_type'] === 'satna') {
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
        } else {
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

    public function convertCardNumberToShabaNumber($requestData): \Illuminate\Http\Client\Response
    {
        return Http::post("{$requestData['gateway']['base_url']}/convert-card-number-to-shaba-number", $requestData);
    }

    public function buildRequestDataForConvertCardNumberToShabaNumber($requestData): array
    {
        return [
            'merchant_id' => $requestData['merchant_id'],
            'username' => $requestData['username'],
            'password' => $requestData['password'],
            'cardNumber' => $requestData['cardNumber'],
            'gateway' => $requestData['gateway'],
            'callback' => $requestData['callback'],
        ];
    }

    public function getResponseCardNumberToShabaNumber($response ,$requestData): array
    {
        if ($response->successful()) {
            return [
                'status' => $response->json('status'),
                'message' => $response->json('message'),
                'shabaNumber' => $response->json('shabaNumber'),
                'redirect_url' => $response->json('redirect_url'),
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
