<?php

namespace App\Helpers\ApiClasses\Interfaces;

use Illuminate\Http\Client\Response;

interface IOpenbanking
{
    public function buildRequestData($requestData): array;

    public function sendToOpenBanking($gateway, $requestData): Response;

    public function getResponseData($response, $requestData);

    public function buildRequestDataForConvertCardNumberToShabaNumber($requestData): array;

    public function convertCardNumberToShabaNumber($requestData): Response;

    public function getResponseCardNumberToShabaNumber($response ,$requestData): array;

}
