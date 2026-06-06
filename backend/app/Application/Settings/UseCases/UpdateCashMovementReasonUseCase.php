<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateCashMovementReasonUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly CashMovementReasonRepositoryInterface $reasons,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! is_object($input)) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $id = (int) ($input->id ?? 0);
        $existing = $this->reasons->findById($id, $tenant->id);

        if ($existing === null) {
            throw MasterDataDomainException::notFound();
        }

        $name = trim((string) ($input->name ?? ''));

        if ($name === '') {
            return OperationResult::fail('El nombre es obligatorio.');
        }

        if ($this->reasons->nameExists($tenant->id, $existing['type'], $name, $id)) {
            throw MasterDataDomainException::duplicate();
        }

        $item = $this->reasons->update($id, $tenant->id, $name, (string) ($input->status ?? 'active'));

        return OperationResult::ok('Motivo actualizado.', ['cash_movement_reason' => $item]);
    }
}
