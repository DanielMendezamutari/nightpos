<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ChargeOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'string', Rule::in(['CASH', 'QR', 'CARD'])],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
