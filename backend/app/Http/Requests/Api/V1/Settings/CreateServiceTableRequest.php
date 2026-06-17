<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class CreateServiceTableRequest extends FormRequest
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
            'service_area_id' => ['required', 'integer', 'min:1'],
            'code' => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
