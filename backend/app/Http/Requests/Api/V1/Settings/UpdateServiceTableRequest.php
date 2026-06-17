<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateServiceTableRequest extends FormRequest
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
            'label' => ['required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
