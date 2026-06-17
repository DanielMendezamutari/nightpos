<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\ServiceTableRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceTableModel;

final class EloquentServiceTableRepository implements ServiceTableRepositoryInterface
{
    public function listForBranch(
        int $tenantId,
        int $branchId,
        bool $activeOnly = false,
        ?int $serviceAreaId = null,
    ): array {
        $query = ServiceTableModel::query()
            ->with('serviceArea:id,name,code')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        if ($serviceAreaId !== null && $serviceAreaId > 0) {
            $query->where('service_area_id', $serviceAreaId);
        }

        return $query->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (ServiceTableModel $m) => $this->map($m))
            ->all();
    }

    public function findById(int $id, int $tenantId, int $branchId): ?array
    {
        $model = ServiceTableModel::query()
            ->with('serviceArea:id,name,code')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function create(
        int $tenantId,
        int $branchId,
        int $serviceAreaId,
        string $code,
        string $label,
        int $sortOrder,
        string $status,
    ): array {
        $areaExists = ServiceAreaModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('id', $serviceAreaId)
            ->exists();

        if (! $areaExists) {
            throw new \InvalidArgumentException('Salón no encontrado.');
        }

        $model = ServiceTableModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'service_area_id' => $serviceAreaId,
            'code' => strtoupper(trim($code)),
            'label' => trim($label),
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        return $this->map($model->fresh(['serviceArea']));
    }

    public function update(
        int $id,
        int $tenantId,
        int $branchId,
        string $label,
        int $sortOrder,
        string $status,
    ): array {
        $model = ServiceTableModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $model->update([
            'label' => trim($label),
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);

        return $this->map($model->fresh(['serviceArea']));
    }

    public function codeExists(int $branchId, string $code, ?int $exceptId = null): bool
    {
        $query = ServiceTableModel::query()
            ->where('branch_id', $branchId)
            ->where('code', strtoupper(trim($code)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function map(ServiceTableModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'service_area_id' => (int) $model->service_area_id,
            'service_area_name' => $model->serviceArea?->name,
            'service_area_code' => $model->serviceArea?->code,
            'code' => $model->code,
            'label' => $model->label,
            'sort_order' => (int) $model->sort_order,
            'status' => $model->status,
        ];
    }
}
