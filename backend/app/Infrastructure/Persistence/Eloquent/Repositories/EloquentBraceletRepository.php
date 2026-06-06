<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\GirlIncome\Support\GirlIncomeMapper;
use App\Domain\GirlIncome\Repositories\BraceletRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;

final class EloquentBraceletRepository implements BraceletRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?array
    {
        $model = BraceletModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? GirlIncomeMapper::bracelet($model) : null;
    }

    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        return BraceletModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BraceletModel $m) => GirlIncomeMapper::bracelet($m))
            ->all();
    }

    public function summarizeForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $row = BraceletModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COUNT(*) as count')
            ->first();

        $total = (float) ($row->total_amount ?? 0);
        $quantity = (int) ($row->quantity ?? 0);
        $count = (int) ($row->count ?? 0);
        $average = $count > 0 ? round($total / $count, 2) : 0.0;

        return [
            'total_amount' => $total,
            'quantity' => $quantity,
            'count' => $count,
            'average' => $average,
        ];
    }

    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $girlUserId,
        ?int $waiterUserId,
        int $quantity,
        string $unitPrice,
        string $totalAmount,
        int $registeredByUserId,
        string $registeredAt,
        ?string $notes,
        int $cashSessionId,
        string $paymentMethod,
    ): array {
        $model = BraceletModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_session_id' => $cashSessionId,
            'girl_user_id' => $girlUserId,
            'waiter_user_id' => $waiterUserId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'registered_by_user_id' => $registeredByUserId,
            'registered_at' => $registeredAt,
            'notes' => $notes,
        ]);

        return GirlIncomeMapper::bracelet($model->fresh());
    }

    public function attachCashMovement(int $id, int $tenantId, int $cashMovementId): void
    {
        BraceletModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['cash_movement_id' => $cashMovementId]);
    }
}
