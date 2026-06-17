<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Cash;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterCashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'movement_type' => ['required', 'string', Rule::in(['INCOME', 'EXPENSE'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'cash_movement_reason_id' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'string', Rule::in(['CASH', 'QR', 'CARD', 'OTHER'])],
        ];
    }
}
