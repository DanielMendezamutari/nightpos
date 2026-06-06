<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Cash;

use Illuminate\Foundation\Http\FormRequest;

final class OpenCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'cash_register_id' => ['nullable', 'integer', 'exists:cash_registers,id'],
            'opening_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
