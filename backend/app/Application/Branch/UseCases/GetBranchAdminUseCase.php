<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Application\Branch\Support\BranchAdminMapper;
use App\Domain\Branch\Exceptions\BranchNotFoundException;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\Auth\Exceptions\TenantAccessDeniedException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetBranchAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $branchId = is_object($input) && isset($input->branchId) ? (int) $input->branchId : 0;
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $branch = $this->branches->findById($branchId);

        if ($branch === null) {
            throw new BranchNotFoundException();
        }

        if ($branch->tenantId !== $tenant->id) {
            throw TenantAccessDeniedException::create();
        }

        return OperationResult::ok('Sucursal obtenida.', [
            'branch' => BranchAdminMapper::branch($branch),
        ]);
    }
}
