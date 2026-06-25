<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Room\Support\RoomMapper;
use App\Domain\Room\Enums\RoomStatus;
use App\Domain\Room\Repositories\RoomRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use Illuminate\Support\Facades\DB;

final class EloquentRoomRepository implements RoomRepositoryInterface
{
    public function findById(int $id, int $tenantId, int $branchId): ?array
    {
        $model = RoomModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        return $model ? RoomMapper::room($model) : null;
    }

    public function listForBranch(int $tenantId, int $branchId, ?string $status = null): array
    {
        $query = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderBy('code');

        if ($status !== null && $status !== '') {
            $query->where('status', strtoupper($status));
        }

        return $query->get()
            ->map(static fn (RoomModel $model) => RoomMapper::room($model))
            ->all();
    }

    public function listAvailable(int $tenantId, int $branchId): array
    {
        return $this->listForBranch($tenantId, $branchId, RoomStatus::Available->value);
    }

    public function listCleaningOverview(int $tenantId, int $branchId): array
    {
        $rooms = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', RoomStatus::Cleaning->value)
            ->orderBy('code')
            ->get();

        return $rooms->map(function (RoomModel $room) use ($tenantId, $branchId) {
            $lastService = RoomServiceModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('room_id', $room->id)
                ->where('status', 'FINISHED')
                ->orderByDesc('ended_at')
                ->with('cleaningUser')
                ->first();

            $minutesSinceFinish = null;
            if ($lastService?->ended_at !== null) {
                $minutesSinceFinish = (int) $lastService->ended_at->diffInMinutes(now());
            }

            return RoomMapper::room($room, [
                'last_finished_at' => $lastService?->ended_at?->format('Y-m-d H:i:s'),
                'minutes_since_finish' => $minutesSinceFinish,
                'cleaning_user_id' => $lastService?->cleaning_user_id,
                'cleaning_user_name' => $lastService?->cleaningUser?->name,
            ]);
        })->all();
    }

    public function statusSummary(int $tenantId, int $branchId): array
    {
        $rows = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $available = (int) ($rows[RoomStatus::Available->value] ?? 0);
        $occupied = (int) ($rows[RoomStatus::Occupied->value] ?? 0);
        $cleaning = (int) ($rows[RoomStatus::Cleaning->value] ?? 0);
        $maintenance = (int) ($rows[RoomStatus::Maintenance->value] ?? 0);

        return [
            'available' => $available,
            'occupied' => $occupied,
            'cleaning' => $cleaning,
            'maintenance' => $maintenance,
            'total' => $available + $occupied + $cleaning + $maintenance,
        ];
    }

    public function codeExists(int $tenantId, int $branchId, string $code, ?int $excludeId = null): bool
    {
        $query = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function create(
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $roomType,
        ?int $defaultDurationMinutes,
        ?string $suggestedPrice,
        ?string $notes,
    ): array {
        $model = RoomModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'code' => $code,
            'name' => $name,
            'room_type' => $roomType,
            'status' => RoomStatus::Available->value,
            'default_duration_minutes' => $defaultDurationMinutes,
            'suggested_price' => $suggestedPrice,
            'notes' => $notes,
        ]);

        return RoomMapper::room($model->fresh());
    }

    public function update(
        int $id,
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $roomType,
        ?int $defaultDurationMinutes,
        ?string $suggestedPrice,
        ?string $notes,
    ): ?array {
        $model = RoomModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update([
            'code' => $code,
            'name' => $name,
            'room_type' => $roomType,
            'default_duration_minutes' => $defaultDurationMinutes,
            'suggested_price' => $suggestedPrice,
            'notes' => $notes,
        ]);

        return RoomMapper::room($model->fresh());
    }

    public function occupyIfAvailable(int $roomId, int $tenantId, int $branchId): bool
    {
        return DB::transaction(function () use ($roomId, $tenantId, $branchId) {
            $updated = RoomModel::query()
                ->where('id', $roomId)
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('status', RoomStatus::Available->value)
                ->update(['status' => RoomStatus::Occupied->value]);

            return $updated === 1;
        });
    }

    public function setCleaning(int $roomId, int $tenantId, int $branchId): bool
    {
        $updated = RoomModel::query()
            ->where('id', $roomId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', RoomStatus::Occupied->value)
            ->update(['status' => RoomStatus::Cleaning->value]);

        return $updated === 1;
    }

    public function releaseAfterService(int $roomId, int $tenantId, int $branchId): bool
    {
        $updated = RoomModel::query()
            ->where('id', $roomId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', RoomStatus::Occupied->value)
            ->update(['status' => RoomStatus::Available->value]);

        return $updated === 1;
    }

    public function markClean(int $roomId, int $tenantId, int $branchId): ?array
    {
        $model = RoomModel::query()
            ->where('id', $roomId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', RoomStatus::Cleaning->value)
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update(['status' => RoomStatus::Available->value]);

        return RoomMapper::room($model->fresh());
    }

    public function markMaintenance(int $roomId, int $tenantId, int $branchId): ?array
    {
        $model = RoomModel::query()
            ->where('id', $roomId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', [RoomStatus::Available->value, RoomStatus::Cleaning->value])
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update(['status' => RoomStatus::Maintenance->value]);

        return RoomMapper::room($model->fresh());
    }

    public function markAvailable(int $roomId, int $tenantId, int $branchId): ?array
    {
        $model = RoomModel::query()
            ->where('id', $roomId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', RoomStatus::Maintenance->value)
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update(['status' => RoomStatus::Available->value]);

        return RoomMapper::room($model->fresh());
    }
}
