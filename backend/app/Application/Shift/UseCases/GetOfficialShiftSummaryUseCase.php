<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Application\Reports\Services\ShiftManagerialSummaryBuilder;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Exceptions\OfficialShiftNotFoundException;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\Shift\ValueObjects\OfficialShiftStatus;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetOfficialShiftSummaryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly ComboBraceletReportingService $comboReporting,
        private readonly ShiftManagerialSummaryBuilder $managerial,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $shiftId = is_object($input) && isset($input->shiftId) ? (int) $input->shiftId : 0;
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $shift = $this->shifts->findById($shiftId, $tenant->id);

        if ($shift === null || $shift->branchId !== $branch->id) {
            throw new OfficialShiftNotFoundException();
        }

        $totals = $this->shifts->buildSummaryTotals($shift->id, $tenant->id, $branch->id);
        $closure = $this->shifts->findClosureByShiftId($shift->id, $tenant->id);

        return OperationResult::ok('Resumen del turno.', [
            'shift' => ShiftMapper::shift($shift, $closure),
            'summary' => array_merge($totals, [
                'counted_cash' => $closure?->countedCash,
                'cash_difference' => $closure?->cashDifference,
                'total_waiter_payouts' => $closure?->totalWaiterPayouts,
                'total_girl_payouts' => $closure?->totalGirlPayouts,
                'is_open' => $shift->status === OfficialShiftStatus::OPEN,
            ]),
            'managerial' => $this->managerial->forShift($tenant->id, $branch->id, $shift->id, $closure),
            'combo_bracelets' => $this->comboReporting->buildScopeSummary($tenant->id, $branch->id, [
                'official_shift_id' => $shift->id,
            ]),
        ]);
    }
}
