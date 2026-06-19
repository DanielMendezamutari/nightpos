<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterPrintDeviceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'paper_width_mm' => ['sometimes', 'integer', 'in:58,80'],
            'auto_print_order' => ['sometimes', 'boolean'],
        ];
    }
}
