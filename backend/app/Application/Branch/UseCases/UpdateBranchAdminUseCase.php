<?php

declare(strict_types=1);

namespace App\Application\Branch\UseCases;

use App\Application\Branch\DTOs\UpdateBranchInput;
use App\Application\Branch\Support\BranchAdminMapper;
use App\Domain\Auth\Exceptions\TenantAccessDeniedException;
use App\Domain\Branch\Exceptions\BranchDomainException;
use App\Domain\Branch\Exceptions\BranchNotFoundException;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateBranchAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdateBranchInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $existing = $this->branches->findById($input->branchId);

        if ($existing === null) {
            throw new BranchNotFoundException();
        }

        if ($existing->tenantId !== $tenant->id) {
            throw TenantAccessDeniedException::create();
        }

        $name = trim($input->name);
        $code = strtoupper(trim($input->code));

        if ($name === '') {
            throw BranchDomainException::emptyName();
        }

        if ($this->branches->codeExistsForTenant($tenant->id, $code, $input->branchId)) {
            throw BranchDomainException::duplicateCode();
        }

        $branch = $this->branches->update(
            id: $input->branchId,
            name: $name,
            code: $code,
            address: $input->address !== null && $input->address !== '' ? trim($input->address) : null,
            status: $input->status,
        );

        return OperationResult::ok('Sucursal actualizada correctamente.', [
            'branch' => BranchAdminMapper::branch($branch),
        ]);
    }
}
