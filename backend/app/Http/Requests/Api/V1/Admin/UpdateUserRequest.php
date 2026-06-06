<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'staff_role' => ['nullable', 'string', 'in:WAITER,GIRL,CASHIER,CLEANING,MANAGER,INVENTORY,REPORTS'],
            'waiter_commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'can_receive_girl_commissions' => ['nullable', 'boolean'],
            'cleaning_base_amount' => ['nullable', 'numeric', 'min:0'],
            'cleaning_room_amount' => ['nullable', 'numeric', 'min:0'],
            'accessible_branch_ids' => ['nullable', 'array'],
            'accessible_branch_ids.*' => ['integer', 'exists:branches,id'],
        ];
    }
}
