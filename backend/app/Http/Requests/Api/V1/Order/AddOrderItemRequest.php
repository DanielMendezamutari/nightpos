<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AddOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'sale_mode' => ['required', 'string', Rule::in(['SOLO_CLIENTE', 'CON_ACOMPANANTE'])],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'girl_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
