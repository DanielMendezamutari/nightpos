<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\DTOs\CloseOfficialShiftInput;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Exceptions\OfficialShiftNotFoundException;
use App\Domain\Shift\Exceptions\ShiftDomainException;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\Shift\ValueObjects\OfficialShiftStatus;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class CloseOfficialShiftUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CloseOfficialShiftInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $shift = $this->shifts->findById($input->shiftId, $tenant->id);

        if ($shift === null || $shift->branchId !== $branch->id) {
            throw new OfficialShiftNotFoundException();
        }

        if ($shift->status !== OfficialShiftStatus::OPEN) {
            throw ShiftDomainException::shiftAlreadyClosed();
        }

        if ($this->shifts->hasOpenCashSessions($shift->id)) {
            throw ShiftDomainException::openCashSessionsExist();
        }

        $totals  = $this->shifts->buildSummaryTotals($shift->id, $tenant->id, $branch->id);
        $counted = number_format((float) $input->countedCash, 2, '.', '');
        $expected = (float) $totals['expected_cash'];
        $difference = number_format((float) $counted - $expected, 2, '.', '');

        // Snapshot de liquidaciones al cierre
        $settlementOverview = $this->settlements->getCurrentShiftOverview(
            $tenant->id,
            $branch->id,
            $shift->id,
            null,
        );
        $settlementSummary = $settlementOverview['summary'] ?? [];
        $totals['total_waiter_payouts']  = $settlementSummary['total_waiters']  ?? null;
        $totals['total_girl_payouts']    = $settlementSummary['total_girls']    ?? null;
        $totals['total_cleaning_payouts'] = $settlementSummary['total_cleaning'] ?? null;

        $result = DB::transaction(function () use ($tenant, $branch, $shift, $userId, $totals, $counted, $difference, $input) {
            $closure = $this->shifts->createClosure(
                tenantId: $tenant->id,
                branchId: $branch->id,
                officialShiftId: $shift->id,
                totals: $totals,
                countedCash: $counted,
                cashDifference: $difference,
                closedByUserId: $userId,
                notes: $input->notes,
            );

            $closed = $this->shifts->close($shift->id, $tenant->id, $userId);

            return ['shift' => $closed, 'closure' => $closure];
        });

        $this->audit->record(
            'official_shift.closed',
            'official_shift',
            $result['shift']->id,
            [
                'counted_cash'           => $counted,
                'cash_difference'        => $difference,
                'total_sales'            => $totals['total_sales'] ?? null,
                'total_waiter_payouts'   => $totals['total_waiter_payouts'],
                'total_girl_payouts'     => $totals['total_girl_payouts'],
                'total_cleaning_payouts' => $totals['total_cleaning_payouts'],
            ],
        );

        return OperationResult::ok('Turno cerrado correctamente.', [
            'shift'   => ShiftMapper::shift($result['shift'], $result['closure']),
            'closure' => ShiftMapper::closure($result['closure']),
        ]);
    }
}
