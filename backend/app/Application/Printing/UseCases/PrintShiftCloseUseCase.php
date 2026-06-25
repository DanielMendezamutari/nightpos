<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintShiftCloseUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CreateShiftClosePrintJobUseCase $createPrintJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $shiftId = (int) ($input->shiftId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);

        $idempotencyKey = $reprint
            ? "shift_close:{$shiftId}:reprint:".now()->timestamp
            : "shift_close:{$shiftId}:v1";

        $printResult = $this->createPrintJob->execute(
            shiftId: $shiftId,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            idempotencyKey: $idempotencyKey,
        );

        if ($printResult['warning'] !== null && str_contains($printResult['warning'], 'aún no tiene cierre')) {
            return OperationResult::fail($printResult['warning']);
        }

        return OperationResult::ok('Cierre de turno encolado.', [
            'official_shift_id' => $shiftId,
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }
}
