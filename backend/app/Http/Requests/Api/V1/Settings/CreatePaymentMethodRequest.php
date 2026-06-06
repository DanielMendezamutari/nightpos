<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in(['CASH', 'QR', 'CARD', 'OTHER'])],
            'enabled' => ['nullable', 'boolean'],
            'requires_reference' => ['nullable', 'boolean'],
            'branch_scoped' => ['nullable', 'boolean'],
        ];
    }
}
