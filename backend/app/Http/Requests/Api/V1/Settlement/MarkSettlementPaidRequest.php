<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settlement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class MarkSettlementPaidRequest extends FormRequest
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
            'payment_method' => ['required', 'string', Rule::in(['CASH', 'QR', 'CARD'])],
            'notes' => ['nullable', 'string', 'max:500'],
            'applied_fine_ids' => ['nullable', 'array'],
            'applied_fine_ids.*' => ['integer', 'min:1'],
        ];
    }
}

