<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel;
use App\Shared\Domain\Enums\PrintDeviceStatus;

final class EloquentPrintDeviceRepository implements PrintDeviceRepositoryInterface
{
    public function create(
        int $tenantId,
        int $branchId,
        string $name,
        string $deviceKeyHash,
        string $deviceKeyPrefix,
        int $paperWidthMm,
        bool $autoPrintOrder,
    ): array {
        $model = PrintDeviceModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $name,
            'device_key_hash' => $deviceKeyHash,
            'device_key_prefix' => $deviceKeyPrefix,
            'status' => PrintDeviceStatus::Active->value,
            'enabled' => true,
            'paper_width_mm' => $paperWidthMm,
            'auto_print_order' => $autoPrintOrder,
        ]);

        return $this->map($model);
    }

    public function findById(int $id, int $tenantId, int $branchId): ?array
    {
        $model = PrintDeviceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function findByKeyPrefix(string $prefix): ?array
    {
        $model = PrintDeviceModel::query()
            ->where('device_key_prefix', $prefix)
            ->where('status', PrintDeviceStatus::Active->value)
            ->first();

        return $model ? $this->map($model, includeHash: true) : null;
    }

    public function listByBranch(int $tenantId, int $branchId): array
    {
        return PrintDeviceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get()
            ->map(fn (PrintDeviceModel $model) => $this->map($model))
            ->all();
    }

    public function hasActiveDevice(int $tenantId, int $branchId): bool
    {
        return PrintDeviceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', PrintDeviceStatus::Active->value)
            ->where('enabled', true)
            ->exists();
    }

    public function update(int $id, int $tenantId, int $branchId, array $fields): ?array
    {
        $model = PrintDeviceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        if ($model === null) {
            return null;
        }

        $model->fill($fields);
        $model->save();

        return $this->map($model->fresh());
    }

    public function rotateKey(int $id, int $tenantId, int $branchId, string $hash, string $prefix): ?array
    {
        $model = PrintDeviceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        if ($model === null) {
            return null;
        }

        $model->device_key_hash = $hash;
        $model->device_key_prefix = $prefix;
        $model->save();

        return $this->map($model->fresh());
    }

    public function recordHeartbeat(
        int $id,
        int $tenantId,
        int $branchId,
        ?string $printerName,
        ?string $agentVersion,
        ?string $lastError,
    ): void {
        PrintDeviceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->update([
                'last_seen_at' => now(),
                'printer_name' => $printerName,
                'agent_version' => $agentVersion,
                'last_error' => $lastError,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function map(PrintDeviceModel $model, bool $includeHash = false): array
    {
        $data = [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'name' => $model->name,
            'device_key_prefix' => $model->device_key_prefix,
            'status' => $model->status,
            'enabled' => (bool) $model->enabled,
            'printer_name' => $model->printer_name,
            'paper_width_mm' => (int) $model->paper_width_mm,
            'auto_print_order' => (bool) $model->auto_print_order,
            'last_seen_at' => $model->last_seen_at?->toIso8601String(),
            'last_error' => $model->last_error,
            'agent_version' => $model->agent_version,
            'online' => $this->isOnline($model),
            'created_at' => $model->created_at?->toIso8601String(),
            'updated_at' => $model->updated_at?->toIso8601String(),
        ];

        if ($includeHash) {
            $data['device_key_hash'] = $model->device_key_hash;
        }

        return $data;
    }

    private function isOnline(PrintDeviceModel $model): bool
    {
        if ($model->last_seen_at === null) {
            return false;
        }

        return $model->last_seen_at->greaterThan(now()->subSeconds(30));
    }
}
