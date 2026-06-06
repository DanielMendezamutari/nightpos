<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Notification\Support\NotificationMapper;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function existsForSource(string $type, string $sourceType, int $sourceId): bool
    {
        return NotificationModel::query()
            ->where('type', $type)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', ['UNREAD', 'READ'])
            ->exists();
    }

    public function existsForSourceRole(string $type, string $sourceType, int $sourceId, string $roleTarget): bool
    {
        return NotificationModel::query()
            ->where('type', $type)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('role_target', $roleTarget)
            ->whereIn('status', ['UNREAD', 'READ'])
            ->exists();
    }

    public function create(array $data): array
    {
        $model = NotificationModel::query()->create(array_merge($data, [
            'sent_at' => $data['sent_at'] ?? Carbon::now(),
            'status' => $data['status'] ?? 'UNREAD',
        ]));

        return NotificationMapper::toArray($model);
    }

    public function listForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
        int $limit,
    ): array {
        return $this->scopedQuery($tenantId, $branchId, $userId, $roleTarget, $managerView)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (NotificationModel $m) => NotificationMapper::toArray($m))
            ->all();
    }

    public function countUnreadForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
    ): int {
        return $this->scopedQuery($tenantId, $branchId, $userId, $roleTarget, $managerView)
            ->where('status', 'UNREAD')
            ->count();
    }

    public function markRead(int $id, int $tenantId, int $branchId): bool
    {
        $updated = NotificationModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'UNREAD')
            ->update([
                'status' => 'READ',
                'read_at' => Carbon::now(),
            ]);

        return $updated > 0;
    }

    public function markReadForRoomSource(int $tenantId, int $branchId, int $roomServiceId): void
    {
        NotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('type', 'ROOM_SERVICE_DUE')
            ->where('source_type', 'ROOM_SERVICE')
            ->where('source_id', $roomServiceId)
            ->where('status', 'UNREAD')
            ->update([
                'status' => 'READ',
                'read_at' => Carbon::now(),
            ]);
    }

    public function markAllReadForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
    ): int {
        return $this->scopedQuery($tenantId, $branchId, $userId, $roleTarget, $managerView)
            ->where('status', 'UNREAD')
            ->update([
                'status' => 'READ',
                'read_at' => Carbon::now(),
            ]);
    }

    private function scopedQuery(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
    ): Builder {
        $query = NotificationModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        if ($managerView) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($userId, $roleTarget) {
            if ($userId !== null) {
                $q->where('user_id', $userId);
            }

            if ($roleTarget !== null) {
                $q->orWhere('role_target', $roleTarget);
            }
        });
    }
}
