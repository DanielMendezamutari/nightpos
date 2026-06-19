<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePrintDeviceRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:100'],
            'enabled' => ['sometimes', 'boolean'],
            'auto_print_order' => ['sometimes', 'boolean'],
            'paper_width_mm' => ['sometimes', 'integer', 'in:58,80'],
            'status' => ['sometimes', 'string', 'in:ACTIVE,DISABLED,REVOKED'],
        ];
    }
}
