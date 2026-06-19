<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

final class MarkPrintJobFailedRequest extends FormRequest
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
            'error' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
