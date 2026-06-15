<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:100', 'unique:tenants,slug'],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
            'plan_name' => ['nullable', 'string', 'max:50'],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date', 'after_or_equal:subscription_starts_at'],
            'branch' => ['required', 'array'],
            'branch.name' => ['required', 'string', 'max:150'],
            'branch.code' => ['required', 'string', 'max:50'],
            'branch.address' => ['nullable', 'string', 'max:255'],
            'branch.status' => ['nullable', 'string', 'in:active,inactive'],
            'admin' => ['required', 'array'],
            'admin.name' => ['required', 'string', 'max:150'],
            'admin.username' => ['required', 'string', 'max:100'],
            'admin.email' => ['nullable', 'email', 'max:150'],
            'admin.password' => ['required', 'string', 'min:6'],
            'admin.pin' => ['nullable', 'string', 'regex:/^\d{4,6}$/'],
        ];
    }
}
