<?php

declare(strict_types=1);

namespace App\Application\Tenant\UseCases;

use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCurrentTenantUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            if ($this->staffContext->isSuperAdmin()) {
                return OperationResult::ok('Superadmin en modo global (sin empresa seleccionada).', [
                    'tenant' => null,
                ]);
            }

            return OperationResult::fail('No hay empresa activa en el contexto.');
        }

        return OperationResult::ok('Empresa actual.', [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'plan_name' => $tenant->planName,
            'subscription_starts_at' => $tenant->subscriptionStartsAt?->format('c'),
            'subscription_ends_at' => $tenant->subscriptionEndsAt?->format('c'),
        ]);
    }
}
