<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Entities\ShiftClosure;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\Shift\ValueObjects\OfficialShiftStatus;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalePaymentModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShiftClosureModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EloquentOfficialShiftRepository implements OfficialShiftRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?OfficialShift
    {
        $model = OfficialShiftModel::query()
            ->with(['openedBy', 'closedBy', 'branch'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->mapShift($model) : null;
    }

    public function findOpenForBranch(int $tenantId, int $branchId): ?OfficialShift
    {
        $model = OfficialShiftModel::query()
            ->with(['openedBy', 'branch'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', OfficialShiftStatus::OPEN)
            ->first();

        return $model ? $this->mapShift($model) : null;
    }

    public function listForBranch(int $tenantId, int $branchId, int $limit = 50): array
    {
        return OfficialShiftModel::query()
            ->with(['openedBy', 'closedBy', 'closure'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (OfficialShiftModel $model) => $this->mapShift($model))
            ->all();
    }

    public function open(
        int $tenantId,
        int $branchId,
        string $name,
        string $shiftType,
        string $businessDate,
        string $startsAt,
        string $endsAt,
        int $openedByUserId,
        ?string $notes,
    ): OfficialShift {
        $model = OfficialShiftModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $name,
            'shift_type' => $shiftType,
            'business_date' => $businessDate,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => OfficialShiftStatus::OPEN,
            'opened_by_user_id' => $openedByUserId,
            'opened_at' => Carbon::now(),
            'notes' => $notes,
        ]);

        return $this->mapShift($model->fresh(['openedBy', 'branch']));
    }

    public function close(int $shiftId, int $tenantId, int $closedByUserId): OfficialShift
    {
        $model = OfficialShiftModel::query()
            ->where('id', $shiftId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $model->update([
            'status' => OfficialShiftStatus::CLOSED,
            'closed_by_user_id' => $closedByUserId,
            'closed_at' => Carbon::now(),
        ]);

        return $this->mapShift($model->fresh(['openedBy', 'closedBy', 'branch']));
    }

    public function buildSummaryTotals(int $officialShiftId, int $tenantId, int $branchId): array
    {
        $payments = SalePaymentModel::query()
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $officialShiftId)
            ->select('sale_payments.payment_method', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_method')
            ->pluck('total', 'payment_method');

        $totalCash = (float) ($payments['CASH'] ?? 0);
        $totalQr = (float) ($payments['QR'] ?? 0);
        $totalCard = (float) ($payments['CARD'] ?? 0);

        $totalSales = (float) SalePaymentModel::query()
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.official_shift_id', $officialShiftId)
            ->sum('sale_payments.amount');

        $sessionIds = CashSessionModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->pluck('id');

        $manualIncome = 0.0;
        $manualExpense = 0.0;
        $openingCash = 0.0;

        if ($sessionIds->isNotEmpty()) {
            $openingCash = (float) CashSessionModel::query()
                ->whereIn('id', $sessionIds)
                ->sum('opening_amount');

            $manualIncome = (float) CashMovementModel::query()
                ->whereIn('cash_session_id', $sessionIds)
                ->where('movement_type', 'INCOME')
                ->where('description', 'not like', 'Cobro comanda%')
                ->sum('amount');

            $manualExpense = (float) CashMovementModel::query()
                ->whereIn('cash_session_id', $sessionIds)
                ->where('movement_type', 'EXPENSE')
                ->sum('amount');
        }

        $expectedCash = $openingCash + $totalCash + $manualIncome - $manualExpense;

        return [
            'total_cash' => number_format($totalCash, 2, '.', ''),
            'total_qr' => number_format($totalQr, 2, '.', ''),
            'total_card' => number_format($totalCard, 2, '.', ''),
            'total_sales' => number_format($totalSales, 2, '.', ''),
            'total_manual_income' => number_format(max(0, $manualIncome), 2, '.', ''),
            'total_manual_expense' => number_format($manualExpense, 2, '.', ''),
            'expected_cash' => number_format($expectedCash, 2, '.', ''),
        ];
    }

    public function createClosure(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        array $totals,
        string $countedCash,
        string $cashDifference,
        int $closedByUserId,
        ?string $notes,
    ): ShiftClosure {
        $model = ShiftClosureModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'total_cash' => $totals['total_cash'],
            'total_qr' => $totals['total_qr'],
            'total_card' => $totals['total_card'],
            'total_sales' => $totals['total_sales'],
            'total_manual_income' => $totals['total_manual_income'],
            'total_manual_expense' => $totals['total_manual_expense'],
            'total_girl_payouts' => $totals['total_girl_payouts'] ?? null,
            'total_waiter_payouts' => $totals['total_waiter_payouts'] ?? null,
            'total_cleaning_payouts' => $totals['total_cleaning_payouts'] ?? null,
            'expected_cash' => $totals['expected_cash'],
            'counted_cash' => $countedCash,
            'cash_difference' => $cashDifference,
            'status' => 'CLOSED',
            'closed_by_user_id' => $closedByUserId,
            'closed_at' => Carbon::now(),
            'notes' => $notes,
        ]);

        return $this->mapClosure($model);
    }

    public function findClosureByShiftId(int $officialShiftId, int $tenantId): ?ShiftClosure
    {
        $model = ShiftClosureModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->mapClosure($model) : null;
    }

    public function hasOpenCashSessions(int $officialShiftId): bool
    {
        return CashSessionModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'OPEN')
            ->exists();
    }

    private function mapShift(OfficialShiftModel $model): OfficialShift
    {
        return new OfficialShift(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            name: (string) $model->name,
            shiftType: (string) $model->shift_type,
            businessDate: $model->business_date->format('Y-m-d'),
            startsAt: $model->starts_at->format('Y-m-d H:i:s'),
            endsAt: $model->ends_at->format('Y-m-d H:i:s'),
            status: (string) $model->status,
            openedByUserId: (int) $model->opened_by_user_id,
            closedByUserId: $model->closed_by_user_id !== null ? (int) $model->closed_by_user_id : null,
            openedAt: $model->opened_at->format('Y-m-d H:i:s'),
            closedAt: $model->closed_at?->format('Y-m-d H:i:s'),
            notes: $model->notes,
            openedByName: $model->relationLoaded('openedBy') ? $model->openedBy?->name : null,
            closedByName: $model->relationLoaded('closedBy') ? $model->closedBy?->name : null,
            branchName: $model->relationLoaded('branch') ? $model->branch?->name : null,
        );
    }

    private function mapClosure(ShiftClosureModel $model): ShiftClosure
    {
        return new ShiftClosure(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            officialShiftId: (int) $model->official_shift_id,
            totalCash: (string) $model->total_cash,
            totalQr: (string) $model->total_qr,
            totalCard: (string) $model->total_card,
            totalSales: (string) $model->total_sales,
            totalManualIncome: (string) $model->total_manual_income,
            totalManualExpense: (string) $model->total_manual_expense,
            totalGirlPayouts: $model->total_girl_payouts !== null ? (string) $model->total_girl_payouts : null,
            totalWaiterPayouts: $model->total_waiter_payouts !== null ? (string) $model->total_waiter_payouts : null,
            expectedCash: (string) $model->expected_cash,
            countedCash: (string) $model->counted_cash,
            cashDifference: (string) $model->cash_difference,
            status: (string) $model->status,
            closedByUserId: (int) $model->closed_by_user_id,
            closedAt: $model->closed_at->format('Y-m-d H:i:s'),
            notes: $model->notes,
        );
    }
}
