<?php

namespace App\Models\Enums;

enum ResponseCodeEnum: int
{
    case PAID = 200;
    case CANCELED = 10;
    case BLOCKED = 11;
    case UNDER_REVIEW = 12;
    case REVERSED = 13;
    case PENDING = 14;
    case FAILED = 15;
    case INTERNAL_ERROR = 17;
}
