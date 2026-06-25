<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settlement;

use Illuminate\Foundation\Http\FormRequest;

final class PreviewManualDiscountRequest extends FormRequest
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
            'discount_mode' => ['required', 'string', 'in:PERCENT,AMOUNT,percent,amount'],
            'discount_value' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
