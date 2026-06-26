<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Platform;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePlatformOperationsTechnicalProfileRequest extends FormRequest
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
            'branch_id' => ['nullable', 'integer'],
            'primary_pc_name' => ['nullable', 'string', 'max:120'],
            'operating_system' => ['nullable', 'string', 'max:120'],
            'ram' => ['nullable', 'string', 'max:40'],
            'printer_model' => ['nullable', 'string', 'max:120'],
            'printer_connection_type' => ['nullable', 'string', 'max:60'],
            'remote_support_tool' => ['nullable', 'string', 'max:60'],
            'remote_support_id' => ['nullable', 'string', 'max:120'],
            'installer_name' => ['nullable', 'string', 'max:120'],
            'installed_at' => ['nullable', 'date'],
            'installation_notes' => ['nullable', 'string', 'max:5000'],
            'password' => ['prohibited'],
            'remote_support_password' => ['prohibited'],
            'anydesk_password' => ['prohibited'],
            'teamviewer_password' => ['prohibited'],
            'rustdesk_password' => ['prohibited'],
        ];
    }
}
