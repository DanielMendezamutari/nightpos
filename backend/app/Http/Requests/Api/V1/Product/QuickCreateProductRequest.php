<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Product;

use Illuminate\Foundation\Http\FormRequest;

final class QuickCreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'solo_price' => ['required', 'numeric', 'min:0.01'],
            'companion_price' => ['nullable', 'numeric', 'min:0.01'],
            'girl_amount' => ['nullable', 'numeric', 'min:0'],
            'house_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
