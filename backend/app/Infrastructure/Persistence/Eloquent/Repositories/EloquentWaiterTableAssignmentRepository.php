<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\WaiterTableAssignmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceTableModel;
use App\Infrastructure\Persistence\Eloquent\Models\WaiterTableAssignmentModel;
use Illuminate\Support\Carbon;

final class EloquentWaiterTableAssignmentRepository implements WaiterTableAssignmentRepositoryInterface
{
    public function listForBranch(
        int $tenantId,
        int $branchId,
        ?int $waiterUserId = null,
        ?int $serviceAreaId = null,
    ): array {
        $query = WaiterTableAssignmentModel::query()
            ->with(['serviceTable.serviceArea:id,name,code'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('official_shift_id');

        if ($waiterUserId !== null && $waiterUserId > 0) {
            $query->where('waiter_user_id', $waiterUserId);
        }

        if ($serviceAreaId !== null && $serviceAreaId > 0) {
            $query->whereHas('serviceTable', fn ($q) => $q->where('service_area_id', $serviceAreaId));
        }

        return $query->orderBy('waiter_user_id')
            ->get()
            ->map(fn (WaiterTableAssignmentModel $m) => $this->map($m))
            ->all();
    }

    public function listTablesForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?int $officialShiftId,
    ): array {
        $query = WaiterTableAssignmentModel::query()
            ->with(['serviceTable.serviceArea:id,name,code'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('waiter_user_id', $waiterUserId)
            ->whereHas('serviceTable', fn ($q) => $q->where('status', 'active'));

        $query->where(function ($q) use ($officialShiftId) {
            $q->whereNull('official_shift_id');
            if ($officialShiftId !== null && $officialShiftId > 0) {
                $q->orWhere('official_shift_id', $officialShiftId);
            }
        });

        return $query->get()
            ->map(fn (WaiterTableAssignmentModel $m) => $this->mapTableRow($m))
            ->sortBy([
                ['sort_order', 'asc'],
                ['label', 'asc'],
            ])
            ->values()
            ->all();
    }

    public function isTableAssignedToWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        int $serviceTableId,
        ?int $officialShiftId,
    ): bool {
        $query = WaiterTableAssignmentModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('waiter_user_id', $waiterUserId)
            ->where('service_table_id', $serviceTableId);

        $query->where(function ($q) use ($officialShiftId) {
            $q->whereNull('official_shift_id');
            if ($officialShiftId !== null && $officialShiftId > 0) {
                $q->orWhere('official_shift_id', $officialShiftId);
            }
        });

        return $query->exists();
    }

    public function syncForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        array $serviceTableIds,
        int $assignedByUserId,
    ): void {
        $tableIds = array_values(array_unique(array_map('intval', $serviceTableIds)));
        $tableIds = array_values(array_filter($tableIds, static fn (int $id) => $id > 0));

        if ($tableIds !== []) {
            $validCount = ServiceTableModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->whereIn('id', $tableIds)
                ->count();

            if ($validCount !== count($tableIds)) {
                throw new \InvalidArgumentException('Una o más mesas no pertenecen a la sucursal.');
            }
        }

        WaiterTableAssignmentModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('waiter_user_id', $waiterUserId)
            ->whereNull('official_shift_id')
            ->delete();

        $now = Carbon::now();

        foreach ($tableIds as $tableId) {
            WaiterTableAssignmentModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'waiter_user_id' => $waiterUserId,
                'service_table_id' => $tableId,
                'official_shift_id' => null,
                'assigned_by_user_id' => $assignedByUserId,
                'assigned_at' => $now,
            ]);
        }
    }

    private function map(WaiterTableAssignmentModel $model): array
    {
        $table = $model->serviceTable;

        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'waiter_user_id' => (int) $model->waiter_user_id,
            'service_table_id' => (int) $model->service_table_id,
            'official_shift_id' => $model->official_shift_id !== null ? (int) $model->official_shift_id : null,
            'assigned_by_user_id' => (int) $model->assigned_by_user_id,
            'assigned_at' => $model->assigned_at?->toIso8601String(),
            'service_table' => $table ? [
                'id' => (int) $table->id,
                'label' => $table->label,
                'code' => $table->code,
                'service_area_id' => (int) $table->service_area_id,
                'service_area_name' => $table->serviceArea?->name,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapTableRow(WaiterTableAssignmentModel $model): array
    {
        $table = $model->serviceTable;

        return [
            'id' => (int) $table->id,
            'label' => $table->label,
            'code' => $table->code,
            'sort_order' => (int) $table->sort_order,
            'service_area_id' => (int) $table->service_area_id,
            'area' => $table->serviceArea?->name ?? '',
        ];
    }
}
