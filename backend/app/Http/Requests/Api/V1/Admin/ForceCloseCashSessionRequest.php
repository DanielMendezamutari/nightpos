<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use App\Domain\Cash\ValueObjects\CashSessionForceCloseReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ForceCloseCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'forced_close_reason' => ['required', 'string', Rule::in(CashSessionForceCloseReason::all())],
            'forced_close_notes' => ['required', 'string', 'min:3', 'max:2000'],
            'declared_closing_amount' => ['nullable'],
        ];
    }
}
