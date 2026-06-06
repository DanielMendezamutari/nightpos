<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Application\Branch\DTOs\CreateBranchInput;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateBranchAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateBranchInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $branch = $this->branches->create(
            tenantId: $tenant->id,
            name: $input->name,
            code: $input->code,
            address: $input->address,
            status: $input->status,
        );

        return OperationResult::ok('Sucursal creada correctamente.', [
            'id' => $branch->id,
            'tenant_id' => $branch->tenantId,
            'name' => $branch->name,
            'code' => $branch->code,
        ]);
    }
}
