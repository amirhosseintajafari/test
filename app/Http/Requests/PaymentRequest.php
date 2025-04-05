<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $creatorId = 1;
        return [
            'amount' => ['required','numeric','min:1000'],
            'callback_url' => ['required','url'],
            'order_id' => ['required','numeric',Rule::unique('transactions')->where(function ($query) use ($creatorId) {
                return $query->where('creator_id', $creatorId);
            })]
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $api_errors = $validator->errors();
        throw new HttpResponseException(response()->json($api_errors, 422));
    }
}
