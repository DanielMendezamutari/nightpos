<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;

final class EloquentCashMovementReasonRepository implements CashMovementReasonRepositoryInterface
{
    public function listForBranch(int $tenantId, int $branchId, ?string $type = null, bool $activeOnly = false): array
    {
        $query = CashMovementReasonModel::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });

        if ($type !== null) {
            $query->where('type', strtoupper($type));
        }

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('type')->orderBy('name')->get()
            ->map(fn (CashMovementReasonModel $m) => $this->map($m))
            ->all();
    }

    public function findById(int $id, int $tenantId): ?array
    {
        $model = CashMovementReasonModel::query()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function create(int $tenantId, ?int $branchId, string $type, string $name, string $status): array
    {
        $model = CashMovementReasonModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'type' => strtoupper($type),
            'name' => trim($name),
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(int $id, int $tenantId, string $name, string $status): array
    {
        $model = CashMovementReasonModel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $model->update([
            'name' => trim($name),
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    public function nameExists(int $tenantId, string $type, string $name, ?int $exceptId = null): bool
    {
        $query = CashMovementReasonModel::query()
            ->where('tenant_id', $tenantId)
            ->where('type', strtoupper($type))
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function map(CashMovementReasonModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => $model->branch_id !== null ? (int) $model->branch_id : null,
            'type' => $model->type,
            'name' => $model->name,
            'status' => $model->status,
        ];
    }
}
