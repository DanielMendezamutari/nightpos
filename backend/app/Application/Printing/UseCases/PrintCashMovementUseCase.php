<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Cash\Services\CashPrintPresenter;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintCashMovementUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CreateCashMovementPrintJobUseCase $createPrintJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        $movementId = (int) ($input->movementId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);
        $presented = CashPrintPresenter::movement($movementId, $tenant->id);

        if ($presented === null) {
            return OperationResult::fail('Movimiento no encontrado.');
        }

        $idempotencyKey = $reprint
            ? "cash_movement:{$movementId}:reprint:".now()->timestamp
            : "cash_movement:{$movementId}:v1";

        $printResult = $this->createPrintJob->execute(
            movementId: $movementId,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            idempotencyKey: $idempotencyKey,
        );

        return OperationResult::ok('Comprobante encolado.', [
            'movement' => $presented['movement'],
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }
}
