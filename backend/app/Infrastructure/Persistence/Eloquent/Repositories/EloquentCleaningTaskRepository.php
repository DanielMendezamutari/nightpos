<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Cleaning\Repositories\CleaningTaskRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;

final class EloquentCleaningTaskRepository implements CleaningTaskRepositoryInterface
{
    public function existsForRoomService(int $roomServiceId): bool
    {
        return CleaningTaskModel::query()
            ->where('room_service_id', $roomServiceId)
            ->exists();
    }

    public function createIfNotExists(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $roomId,
        int $roomServiceId,
        int $cleaningUserId,
        string $amount,
    ): ?array {
        if ($this->existsForRoomService($roomServiceId)) {
            return null;
        }

        $model = CleaningTaskModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'room_id' => $roomId,
            'room_service_id' => $roomServiceId,
            'cleaning_user_id' => $cleaningUserId,
            'amount' => $amount,
            'status' => 'DONE',
            'cleaned_at' => now(),
        ]);

        return $this->map($model->fresh(['room', 'roomService']));
    }

    public function listForShiftAndUser(int $tenantId, int $branchId, int $officialShiftId, int $cleaningUserId): array
    {
        return CleaningTaskModel::query()
            ->with(['room', 'roomService'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('cleaning_user_id', $cleaningUserId)
            ->orderBy('cleaned_at')
            ->get()
            ->map(fn (CleaningTaskModel $m) => $this->map($m))
            ->all();
    }

    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        return CleaningTaskModel::query()
            ->with(['room', 'roomService', 'cleaningUser'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->orderBy('cleaned_at')
            ->get()
            ->map(fn (CleaningTaskModel $m) => $this->map($m))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function map(CleaningTaskModel $model): array
    {
        $room = $model->relationLoaded('room') ? $model->room : null;
        $service = $model->relationLoaded('roomService') ? $model->roomService : null;

        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'official_shift_id' => (int) $model->official_shift_id,
            'room_id' => (int) $model->room_id,
            'room_service_id' => (int) $model->room_service_id,
            'cleaning_user_id' => (int) $model->cleaning_user_id,
            'amount' => number_format((float) $model->amount, 2, '.', ''),
            'status' => $model->status,
            'cleaned_at' => $model->cleaned_at?->format('Y-m-d H:i:s'),
            'room_code' => $room?->code,
            'room_label' => $service?->room_label ?? $room?->name,
        ];
    }
}
