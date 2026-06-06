<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCurrentBranchUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly BranchContextInterface $branchContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $branch = $this->branchContext->branch();

        if ($branch === null) {
            return OperationResult::fail('No hay sucursal activa en el contexto.');
        }

        return OperationResult::ok('Sucursal actual.', [
            'id' => $branch->id,
            'tenant_id' => $branch->tenantId,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'status' => $branch->status,
        ]);
    }
}
