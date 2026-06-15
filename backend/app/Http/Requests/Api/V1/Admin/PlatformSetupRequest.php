<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PlatformSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant' => ['required', 'array'],
            'tenant.name' => ['required', 'string', 'max:150'],
            'tenant.slug' => ['required', 'string', 'max:100', 'unique:tenants,slug'],
            'tenant.status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'suspended'])],
            'tenant.plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'tenant.plan_name' => ['nullable', 'string', 'max:50'],
            'branch' => ['required', 'array'],
            'branch.name' => ['required', 'string', 'max:150'],
            'branch.code' => ['required', 'string', 'max:50'],
            'branch.address' => ['nullable', 'string', 'max:255'],
            'branch.status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'admin' => ['required', 'array'],
            'admin.name' => ['required', 'string', 'max:150'],
            'admin.username' => ['required', 'string', 'max:100'],
            'admin.email' => ['nullable', 'email', 'max:150'],
            'admin.password' => ['required', 'string', 'min:6'],
            'admin.pin' => ['nullable', 'string', 'regex:/^\d{4,6}$/'],
        ];
    }
}
