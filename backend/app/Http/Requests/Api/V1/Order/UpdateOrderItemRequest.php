<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['sometimes', 'integer', 'min:1'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'sale_mode' => ['sometimes', 'string', Rule::in(['SOLO_CLIENTE', 'CON_ACOMPANANTE'])],
            'girl_user_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
