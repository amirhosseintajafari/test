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

    public static function getPaymentStatus(array $response): string
    {
        $statusMessages = [
            ResponseCodeEnum::CANCELED->value => StatusEnum::CANCELED->value,
            ResponseCodeEnum::BLOCKED->value => StatusEnum::BLOCKED->value,
            ResponseCodeEnum::UNDER_REVIEW->value => StatusEnum::UNDER_REVIEW->value,
            ResponseCodeEnum::REVERSED->value => StatusEnum::REVERSED->value,
            ResponseCodeEnum::PENDING->value => StatusEnum::PENDING->value,
            ResponseCodeEnum::FAILED->value => StatusEnum::FAILED->value,
            ResponseCodeEnum::PAID->value => StatusEnum::PAID->value
        ];

        $responseCode = $response['response_code'] ?? null;
        return ($statusMessages[$responseCode] ?? StatusEnum::FAILED->value);
    }
}
