<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\GirlIncome\Support\GirlIncomeMapper;
use App\Domain\GirlIncome\Repositories\ShowRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;

final class EloquentShowRepository implements ShowRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?array
    {
        $model = ShowModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? GirlIncomeMapper::show($model) : null;
    }

    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        return ShowModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->orderByDesc('registered_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ShowModel $m) => GirlIncomeMapper::show($m))
            ->all();
    }

    public function summarizeForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $row = ShowModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->selectRaw('COUNT(*) as count')
            ->first();

        return [
            'total_amount' => (float) ($row->total_amount ?? 0),
            'count' => (int) ($row->count ?? 0),
        ];
    }

    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $girlUserId,
        string $showType,
        string $unitPrice,
        string $totalAmount,
        int $registeredByUserId,
        string $registeredAt,
        ?string $notes,
        int $cashSessionId,
        string $paymentMethod,
    ): array {
        $model = ShowModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_session_id' => $cashSessionId,
            'girl_user_id' => $girlUserId,
            'show_type' => strtoupper($showType),
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'registered_by_user_id' => $registeredByUserId,
            'registered_at' => $registeredAt,
            'notes' => $notes,
        ]);

        return GirlIncomeMapper::show($model->fresh());
    }

    public function attachCashMovement(int $id, int $tenantId, int $cashMovementId): void
    {
        ShowModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['cash_movement_id' => $cashMovementId]);
    }
}
