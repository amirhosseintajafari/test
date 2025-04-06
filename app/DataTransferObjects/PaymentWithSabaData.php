<?php

namespace App\DataTransferObjects;

use Illuminate\Http\Request;

class PaymentWithSabaData
{
    public function __construct(
        public int $amount,
        public int|string $orderId,
        public string $callbackUrl,
        public int $creatorId,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            amount: (int) $request->input('amount'),
            orderId: $request->input('order_id'),
            callbackUrl: $request->input('callback_url'),
            creatorId: auth()->id() ?? 1,
        );
    }
}
