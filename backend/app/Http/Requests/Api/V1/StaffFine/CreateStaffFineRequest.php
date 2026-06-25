<?php



declare(strict_types=1);



namespace App\Http\Requests\Api\V1\StaffFine;



use Illuminate\Foundation\Http\FormRequest;



final class CreateStaffFineRequest extends FormRequest

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

            'staff_user_id' => ['required', 'integer', 'min:1'],

            'staff_role' => ['nullable', 'string', 'max:30'],

            'amount' => ['required', 'numeric', 'gt:0'],

            'reason' => ['required', 'string', 'max:255'],

            'notes' => ['nullable', 'string', 'max:500'],

        ];

    }

}


