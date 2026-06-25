<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashPrintPresenter;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashMovementUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw CashDomainException::branchRequired();
        }

        $movementId = (int) ($input->movementId ?? 0);

        $model = CashMovementModel::query()
            ->where('id', $movementId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($model === null || (int) $model->branch_id !== $branch->id) {
            return OperationResult::fail('Movimiento no encontrado.');
        }

        $presented = CashPrintPresenter::movement($movementId, $tenant->id);

        if ($presented === null) {
            return OperationResult::fail('Movimiento no encontrado.');
        }

        return OperationResult::ok('Movimiento encontrado.', $presented);
    }
}
