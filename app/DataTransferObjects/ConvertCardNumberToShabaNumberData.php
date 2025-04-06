<?php

namespace App\DataTransferObjects;

use Illuminate\Http\Request;

class ConvertCardNumberToShabaNumberData
{
    public function __construct(
        public int $cardNumber,
        public string $callbackUrl,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            cardNumber: $request->input('cardNumber'),
            callbackUrl: $request->input('callbackUrl'),
        );
    }
}
