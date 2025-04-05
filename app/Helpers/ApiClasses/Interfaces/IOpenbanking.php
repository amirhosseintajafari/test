<?php

namespace App\Helpers\ApiClasses\Interfaces;

use Illuminate\Http\Client\Request;

interface IOpenbanking
{
    public function buildRequestData($requestData): array;

    public function sendToOpenBanking($gateway, $requestData);

    public function getResponseData($response, $requestData);

}
