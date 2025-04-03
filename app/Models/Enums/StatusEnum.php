<?php

namespace App\Models\Enums;

enum StatusEnum: string
{
    case PAID = 'paid';
    case CANCELED = 'canceled';
    case FAILED = 'failed';
    case PENDING = 'pending';
    case REVERSED = 'reversed';
    case BLOCKED = 'blocked';
    case UNDER_REVIEW = 'under_review';
    case SEND_TO_BANK = 'send_to_bank';
}
