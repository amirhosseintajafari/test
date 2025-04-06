<?php

namespace App\Models\Enums;

enum PaymentTypeEnum: string
{
    case SATNA = 'satna';
    case PAYA = 'paya';
    case NORMAL = 'normal';
}
