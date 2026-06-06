<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class LoginPasswordRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:4'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
            'tenant_slug' => ['nullable', 'string', 'max:100'],
        ];
    }
}
