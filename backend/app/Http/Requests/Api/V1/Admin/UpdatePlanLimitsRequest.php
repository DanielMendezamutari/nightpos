<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePlanLimitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'limits' => ['required', 'array', 'min:1'],
            'limits.*.limit_key' => ['required', 'string', 'max:50'],
            'limits.*.limit_value' => ['required', 'integer'],
        ];
    }
}
