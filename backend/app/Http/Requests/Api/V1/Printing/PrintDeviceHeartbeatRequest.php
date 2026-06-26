<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Printing;

use Illuminate\Foundation\Http\FormRequest;

final class PrintDeviceHeartbeatRequest extends FormRequest
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
            'printer_name' => ['nullable', 'string', 'max:255'],
            'agent_version' => ['nullable', 'string', 'max:40'],
            'last_error' => ['nullable', 'string', 'max:2000'],
            'host_name' => ['nullable', 'string', 'max:120'],
            'os_name' => ['nullable', 'string', 'max:80'],
            'os_version' => ['nullable', 'string', 'max:80'],
            'arch' => ['nullable', 'string', 'max:40'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'printer_model' => ['nullable', 'string', 'max:120'],
        ];
    }
}
