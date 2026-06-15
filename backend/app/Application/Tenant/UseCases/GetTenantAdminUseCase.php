<?php

declare(strict_types=1);

namespace App\Application\Tenant\UseCases;

use App\Application\Plan\Support\TenantPlanUsageCalculator;
use App\Application\Tenant\Support\TenantAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetTenantAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly TenantPlanUsageCalculator $planUsage,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenantId = is_object($input) && isset($input->tenantId) ? (int) $input->tenantId : 0;

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.list');
        }

        $tenant = $this->tenants->findById($tenantId);

        if ($tenant === null) {
            throw new TenantNotFoundException();
        }

        return OperationResult::ok('Empresa obtenida.', [
            'tenant' => TenantAdminMapper::withPlanUsage($tenant, $this->planUsage),
        ]);
    }
}
