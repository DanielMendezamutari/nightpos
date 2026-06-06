<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListAvailableBranchesUseCase implements UseCaseInterface
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
        $userId = $this->staffContext->userId();

        if ($userId === null) {
            return OperationResult::fail('Contexto de empresa no disponible.');
        }

        if ($tenant === null) {
            if ($this->staffContext->isSuperAdmin()) {
                return OperationResult::ok('Sin empresa en contexto — seleccione tenant para listar sucursales.', [
                    'branches' => [],
                ]);
            }

            return OperationResult::fail('Contexto de empresa no disponible.');
        }

        $items = $this->branches->listAccessibleForUser($userId, $tenant->id);

        $data = array_map(static fn ($branch) => [
            'id' => $branch->id,
            'tenant_id' => $branch->tenantId,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'status' => $branch->status,
        ], $items);

        return OperationResult::ok('Sucursales disponibles.', ['branches' => $data]);
    }
}
