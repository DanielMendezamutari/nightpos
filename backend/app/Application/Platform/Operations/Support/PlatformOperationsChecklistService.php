<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

use App\Infrastructure\Persistence\Eloquent\Models\TenantOperationChecklistItemModel;
use Illuminate\Support\Carbon;

final class PlatformOperationsChecklistService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForTenant(int $tenantId, ?int $branchId = null): array
    {
        $this->ensureDefaults($tenantId, $branchId);

        $query = TenantOperationChecklistItemModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('id');

        if ($branchId === null) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $branchId);
        }

        return $query->get()->map(static fn (TenantOperationChecklistItemModel $item) => [
            'key' => $item->item_key,
            'label' => $item->label,
            'completed' => (bool) $item->completed,
            'completed_at' => $item->completed_at?->toIso8601String(),
            'completed_by_user_id' => $item->completed_by_user_id !== null ? (int) $item->completed_by_user_id : null,
            'notes' => $item->notes,
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patchItem(int $tenantId, string $key, array $payload, ?int $userId): array
    {
        $this->ensureDefaults($tenantId, null);

        $item = TenantOperationChecklistItemModel::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('branch_id')
            ->where('item_key', $key)
            ->firstOrFail();

        if (array_key_exists('completed', $payload)) {
            $completed = (bool) $payload['completed'];
            $item->completed = $completed;
            $item->completed_at = $completed ? Carbon::now() : null;
            $item->completed_by_user_id = $completed ? $userId : null;
        }

        if (array_key_exists('notes', $payload)) {
            $item->notes = $payload['notes'] !== null ? (string) $payload['notes'] : null;
        }

        $item->save();

        return [
            'key' => $item->item_key,
            'label' => $item->label,
            'completed' => (bool) $item->completed,
            'completed_at' => $item->completed_at?->toIso8601String(),
            'completed_by_user_id' => $item->completed_by_user_id !== null ? (int) $item->completed_by_user_id : null,
            'notes' => $item->notes,
        ];
    }

    public function ensureDefaults(int $tenantId, ?int $branchId = null): void
    {
        foreach (PlatformOperationsChecklistCatalog::defaults() as $def) {
            TenantOperationChecklistItemModel::query()->firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'item_key' => $def['key'],
                ],
                [
                    'label' => $def['label'],
                    'completed' => false,
                ],
            );
        }
    }
}
