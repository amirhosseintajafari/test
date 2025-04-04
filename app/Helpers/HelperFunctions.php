<?php

use Illuminate\Support\Facades\Cache;

if (!function_exists('isGatewayAvailable')) {
    function isGatewayAvailable($gatewayName): bool
    {
        return !Cache::has("gateway_unavailable_$gatewayName");
    }
}
