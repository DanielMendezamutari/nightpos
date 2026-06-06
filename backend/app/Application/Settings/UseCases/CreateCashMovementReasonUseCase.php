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

final class CreateCashMovementReasonUseCase implements UseCaseInterface
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

        $type = strtoupper(trim((string) ($input->type ?? '')));
        $name = trim((string) ($input->name ?? ''));

        if (! in_array($type, ['INCOME', 'EXPENSE'], true) || $name === '') {
            return OperationResult::fail('Tipo y nombre son obligatorios.');
        }

        if ($this->reasons->nameExists($tenant->id, $type, $name)) {
            throw MasterDataDomainException::duplicate();
        }

        $item = $this->reasons->create(
            $tenant->id,
            isset($input->branchScoped) && $input->branchScoped ? $branch->id : null,
            $type,
            $name,
            (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Motivo creado.', ['cash_movement_reason' => $item]);
    }
}
