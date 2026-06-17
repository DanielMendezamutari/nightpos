<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Product;

use App\Domain\Product\ValueObjects\SettlementBehavior;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'sku' => ['nullable', 'string', 'max:100'],
            'solo_price' => ['required', 'numeric', 'min:0.01'],
            'companion_price' => ['nullable', 'numeric', 'min:0.01'],
            'girl_amount' => ['nullable', 'numeric', 'min:0'],
            'house_amount' => ['nullable', 'numeric', 'min:0'],
            'product_type' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'settlement_behavior' => ['nullable', 'string', Rule::in([
                SettlementBehavior::GIRL_LINE,
                SettlementBehavior::GIRL_BRACELET_ALLOCATION,
                SettlementBehavior::NONE,
            ])],
            'bracelet_units_per_line' => ['nullable', 'integer', 'min:1', 'max:999'],
        ];
    }
}
