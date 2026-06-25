<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePrintSettingsRequest extends FormRequest
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
            'auto_print_order_command' => ['sometimes', 'boolean'],
            'auto_print_sale_receipt' => ['sometimes', 'boolean'],
        ];
    }
}
