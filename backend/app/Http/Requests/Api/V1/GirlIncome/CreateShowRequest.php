<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\GirlIncome;

use Illuminate\Foundation\Http\FormRequest;

final class CreateShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'girl_user_id' => ['required', 'integer', 'min:1'],
            'show_type' => ['required', 'string', 'max:100'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:20'],
            'registered_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
