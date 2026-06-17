<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

final class SyncWaiterTableAssignmentsRequest extends FormRequest
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
            'waiter_user_id' => ['required', 'integer', 'min:1'],
            'service_table_ids' => ['present', 'array'],
            'service_table_ids.*' => ['integer', 'min:1'],
        ];
    }
}
