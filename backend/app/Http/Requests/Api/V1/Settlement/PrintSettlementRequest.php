<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settlement;

use Illuminate\Foundation\Http\FormRequest;

final class PrintSettlementRequest extends FormRequest
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
            'reprint' => ['sometimes', 'boolean'],
        ];
    }
}
