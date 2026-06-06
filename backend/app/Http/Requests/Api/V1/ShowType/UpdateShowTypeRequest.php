<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\ShowType;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateShowTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
