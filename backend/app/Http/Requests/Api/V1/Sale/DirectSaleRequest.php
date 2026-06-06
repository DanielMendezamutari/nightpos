<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DirectSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.sale_mode' => ['required', 'string', Rule::in(['SOLO_CLIENTE', 'CON_ACOMPANANTE'])],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.girl_user_id' => ['nullable', 'integer', 'min:1'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'string', Rule::in(['CASH', 'QR', 'CARD'])],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
