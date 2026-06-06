<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;

final class EloquentPaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    public function listForBranch(int $tenantId, int $branchId, bool $enabledOnly = false): array
    {
        $query = PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });

        if ($enabledOnly) {
            $query->where('enabled', true);
        }

        return $query->orderBy('code')->get()
            ->map(fn (PaymentMethodModel $m) => $this->map($m))
            ->all();
    }

    public function findById(int $id, int $tenantId): ?array
    {
        $model = PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function findByCode(int $tenantId, int $branchId, string $code): ?array
    {
        $model = PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', strtoupper($code))
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->where('enabled', true)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function create(
        int $tenantId,
        ?int $branchId,
        string $code,
        string $name,
        string $type,
        bool $enabled,
        bool $requiresReference,
    ): array {
        $model = PaymentMethodModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'code' => strtoupper(trim($code)),
            'name' => trim($name),
            'type' => strtoupper($type),
            'enabled' => $enabled,
            'requires_reference' => $requiresReference,
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        int $tenantId,
        string $name,
        bool $enabled,
        bool $requiresReference,
    ): array {
        $model = PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $model->update([
            'name' => trim($name),
            'enabled' => $enabled,
            'requires_reference' => $requiresReference,
        ]);

        return $this->map($model->fresh());
    }

    public function codeExists(int $tenantId, string $code, ?int $exceptId = null): bool
    {
        $query = PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', strtoupper(trim($code)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function hasEnabledCash(int $tenantId, int $branchId): bool
    {
        return PaymentMethodModel::query()
            ->where('tenant_id', $tenantId)
            ->where('type', 'CASH')
            ->where('enabled', true)
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            })
            ->exists();
    }

    public function enabledLegacyCodes(int $tenantId, int $branchId): array
    {
        $codes = [];
        foreach ($this->listForBranch($tenantId, $branchId, true) as $method) {
            $type = $method['type'];
            if (in_array($type, ['CASH', 'QR', 'CARD'], true)) {
                $codes[] = $type;
            }
        }

        return array_values(array_unique($codes));
    }

    private function map(PaymentMethodModel $model): array
    {
        $legacy = in_array($model->type, ['CASH', 'QR', 'CARD'], true)
            ? $model->type
            : 'OTHER';

        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => $model->branch_id !== null ? (int) $model->branch_id : null,
            'code' => $model->code,
            'name' => $model->name,
            'type' => $model->type,
            'legacy_method' => $legacy,
            'enabled' => (bool) $model->enabled,
            'requires_reference' => (bool) $model->requires_reference,
        ];
    }
}
