<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Infrastructure\Persistence\Eloquent\Models\TenantTechnicalProfileModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class GetPlatformOperationsTechnicalProfileUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        $tenantId = (int) ($input->tenantId ?? 0);
        $profile = TenantTechnicalProfileModel::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('branch_id')
            ->first();

        return OperationResult::ok('Perfil técnico.', [
            'profile' => $profile !== null ? $this->map($profile) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function map(TenantTechnicalProfileModel $profile): array
    {
        return [
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
        ];
    }
}
