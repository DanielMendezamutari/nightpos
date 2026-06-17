<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

final class SyncOrderItemAllocationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.girl_user_id' => ['required', 'integer', 'min:1'],
            'allocations.*.units' => ['required', 'integer', 'min:1'],
        ];
    }
}
