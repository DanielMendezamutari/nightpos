<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settlement;

use Illuminate\Foundation\Http\FormRequest;

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
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
