<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListBranchesAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $items = $this->staffContext->isSuperAdmin()
            ? $this->branches->listByTenant($tenant->id)
            : $this->branches->listAccessibleForUser((int) $this->staffContext->userId(), $tenant->id);

        $data = array_map(static fn ($branch) => [
            'id' => $branch->id,
            'tenant_id' => $branch->tenantId,
            'name' => $branch->name,
            'code' => $branch->code,
            'status' => $branch->status,
        ], $items);

        return OperationResult::ok('Listado de sucursales.', ['branches' => $data]);
    }
}
