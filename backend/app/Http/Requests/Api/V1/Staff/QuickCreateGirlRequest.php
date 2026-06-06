<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Staff;

use Illuminate\Foundation\Http\FormRequest;

final class QuickCreateGirlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'pin' => ['nullable', 'string', 'regex:/^\d{4,6}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
