<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Infrastructure\Persistence\Eloquent\Models\TenantTechnicalProfileModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Carbon;

final class UpdatePlatformOperationsTechnicalProfileUseCase implements UseCaseInterface
{
    private const FORBIDDEN_KEYS = [
        'password',
        'remote_support_password',
        'anydesk_password',
        'teamviewer_password',
        'rustdesk_password',
    ];

    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        $tenantId = (int) ($input->tenantId ?? 0);
        /** @var array<string, mixed> $data */
        $data = (array) ($input->data ?? []);

        foreach (self::FORBIDDEN_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                return OperationResult::fail('No se permiten contraseñas en el perfil técnico.');
            }
        }

        $profile = TenantTechnicalProfileModel::query()->firstOrNew([
            'tenant_id' => $tenantId,
            'branch_id' => $data['branch_id'] ?? null,
        ]);

        $allowed = [
            'primary_pc_name',
            'operating_system',
            'ram',
            'printer_model',
            'printer_connection_type',
            'remote_support_tool',
            'remote_support_id',
            'installer_name',
            'installation_notes',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $profile->{$field} = $data[$field];
            }
        }

        if (array_key_exists('installed_at', $data)) {
            $profile->installed_at = $data['installed_at'] !== null
                ? Carbon::parse((string) $data['installed_at'])
                : null;
        }

        $profile->save();

        return OperationResult::ok('Perfil técnico actualizado.', [
            'profile' => [
                'tenant_id' => (int) $profile->tenant_id,
                'branch_id' => $profile->branch_id !== null ? (int) $profile->branch_id : null,
                'primary_pc_name' => $profile->primary_pc_name,
                'operating_system' => $profile->operating_system,
                'ram' => $profile->ram,
                'printer_model' => $profile->printer_model,
                'printer_connection_type' => $profile->printer_connection_type,
                'remote_support_tool' => $profile->remote_support_tool,
                'remote_support_id' => $profile->remote_support_id,
                'installer_name' => $profile->installer_name,
                'installed_at' => $profile->installed_at?->toIso8601String(),
                'installation_notes' => $profile->installation_notes,
            ],
        ]);
    }
}
