<?php



declare(strict_types=1);



namespace App\Http\Requests\Api\V1\GirlIncome;



use Illuminate\Foundation\Http\FormRequest;



final class CreateRoomServiceRequest extends FormRequest

{

    public function authorize(): bool

    {

        return true;

    }



    public function rules(): array

    {

        return [

            'girl_user_id' => ['required', 'integer', 'min:1'],

            'room_id' => ['nullable', 'integer', 'min:1'],

            'room_label' => ['required_without:room_id', 'nullable', 'string', 'max:50'],

            'room_number' => ['nullable', 'string', 'max:30'],

            'total_amount' => ['required_without:unit_price', 'nullable', 'numeric', 'min:0.01'],

            'unit_price' => ['required_without:total_amount', 'nullable', 'numeric', 'min:0.01'],

            'girl_percent' => ['required', 'numeric', 'min:0', 'max:100'],

            'payment_method' => ['required', 'string', 'max:20'],

            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],

            'started_at' => ['nullable', 'date'],

            'cleaning_amount' => ['nullable', 'numeric', 'min:0'],

            'notes' => ['nullable', 'string', 'max:1000'],

        ];

    }

}

