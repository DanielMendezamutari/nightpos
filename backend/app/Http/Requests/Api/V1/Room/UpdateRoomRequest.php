<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Room;

use App\Domain\Room\Enums\RoomType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:100'],
            'room_type' => ['nullable', 'string', 'max:30', Rule::in(RoomType::values())],
            'room_type_id' => ['nullable', 'integer', 'min:1'],
            'default_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
