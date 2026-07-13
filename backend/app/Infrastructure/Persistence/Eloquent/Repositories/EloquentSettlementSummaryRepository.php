<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Cash\Contracts\SettlementSummaryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final class EloquentSettlementSummaryRepository implements SettlementSummaryRepositoryInterface
{
    public function getSettlementBreakdown(
        int $tenantId,
        int $branchId,
        ?int $cashSessionId = null,
        ?int $officialShiftId = null,
    ): array
    {
        $scope = $this->scopedSettlementsQuery($tenantId, $branchId, $cashSessionId, $officialShiftId);

        $settlementRows = (clone $scope)
            ->selectRaw('settlement_type, status, COUNT(*) as cnt, SUM(gross_amount) as gross_total, SUM(adjustments_total) as adjustments_total, SUM(net_amount) as net_total')
            ->groupBy('settlement_type', 'status')
            ->get()
            ->map(fn (StaffSettlementModel $row) => [
                'settlement_type' => $row->getAttribute('settlement_type'),
                'status' => $row->getAttribute('status'),
                'cnt' => (int) $row->getAttribute('cnt'),
                'gross_total' => (string) ($row->getAttribute('gross_total') ?? '0.00'),
                'adjustments_total' => (string) ($row->getAttribute('adjustments_total') ?? '0.00'),
                'net_total' => (string) ($row->getAttribute('net_total') ?? '0.00'),
            ])
            ->toArray();

        $adjustments = StaffSettlementAdjustmentModel::query()
            ->join('staff_settlements as ss', 'ss.id', '=', 'staff_settlement_adjustments.staff_settlement_id')
            ->where('ss.tenant_id', $tenantId)
            ->where('ss.branch_id', $branchId)
            ->whereIn('ss.status', ['PENDING', 'PAID'])
            ->when($cashSessionId !== null, fn ($query) => $query->where('ss.cash_session_id', $cashSessionId))
            ->when($cashSessionId === null && $officialShiftId !== null, fn ($query) => $query->where('ss.official_shift_id', $officialShiftId))
            ->selectRaw('staff_settlement_adjustments.adjustment_type as adjustment_type, COUNT(*) as cnt, SUM(staff_settlement_adjustments.amount) as total')
            ->groupBy('staff_settlement_adjustments.adjustment_type')
            ->get()
            ->map(fn ($row) => [
                'adjustment_type' => $row->getAttribute('adjustment_type'),
                'cnt' => (int) $row->getAttribute('cnt'),
                'total' => (string) ($row->getAttribute('total') ?? '0.00'),
            ])
            ->toArray();

        $manualCompensation = (clone $scope)
            ->where('settlement_type', 'WAITER')
            ->where('compensation_mode', 'MANUAL')
            ->selectRaw('status, COUNT(*) as cnt, SUM(COALESCE(manual_amount_input, net_amount, 0)) as total')
            ->groupBy('status')
            ->get()
            ->map(fn (StaffSettlementModel $row) => [
                'status' => $row->getAttribute('status'),
                'cnt' => (int) $row->getAttribute('cnt'),
                'total' => (string) ($row->getAttribute('total') ?? '0.00'),
            ])
            ->toArray();

        return [
            'settlements' => $settlementRows,
            'adjustments' => $adjustments,
            'manual_compensation' => $manualCompensation,
        ];
    }

    private function scopedSettlementsQuery(
        int $tenantId,
        int $branchId,
        ?int $cashSessionId,
        ?int $officialShiftId,
    ): Builder {
        if ($cashSessionId !== null && $officialShiftId !== null) {
            throw new InvalidArgumentException('cash_session_id and official_shift_id cannot be combined in settlement summary scope.');
        }

        return StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['PENDING', 'PAID'])
            ->when($cashSessionId !== null, fn (Builder $query) => $query->where('cash_session_id', $cashSessionId))
            ->when($cashSessionId === null && $officialShiftId !== null, fn (Builder $query) => $query->where('official_shift_id', $officialShiftId));
    }
}
