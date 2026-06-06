<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\GirlIncome;

use Illuminate\Foundation\Http\FormRequest;

final class CreateBraceletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'girl_user_id' => ['required', 'integer', 'min:1'],
            'waiter_user_id' => ['nullable', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'unit_price' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
