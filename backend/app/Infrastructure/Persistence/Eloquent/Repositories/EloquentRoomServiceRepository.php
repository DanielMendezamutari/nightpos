<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\GirlIncome\Support\GirlIncomeMapper;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use Illuminate\Support\Carbon;

final class EloquentRoomServiceRepository implements RoomServiceRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?array
    {
        $model = RoomServiceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? GirlIncomeMapper::roomService($model) : null;
    }

    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        return RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (RoomServiceModel $m) => GirlIncomeMapper::roomService($m))
            ->all();
    }

    public function listActive(int $tenantId, int $branchId): array
    {
        return RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'ACTIVE')
            ->where('expected_ends_at', '>', Carbon::now(config('app.timezone', 'America/La_Paz')))
            ->orderBy('expected_ends_at')
            ->get()
            ->map(fn (RoomServiceModel $m) => GirlIncomeMapper::roomService($m))
            ->all();
    }

    public function listDue(int $tenantId, int $branchId): array
    {
        $now = Carbon::now(config('app.timezone', 'America/La_Paz'));

        return RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($now) {
                $query->where('status', 'DUE')
                    ->orWhere(function ($nested) use ($now) {
                        $nested->where('status', 'ACTIVE')
                            ->where('expected_ends_at', '<=', $now);
                    });
            })
            ->orderBy('expected_ends_at')
            ->get()
            ->map(fn (RoomServiceModel $m) => GirlIncomeMapper::roomService($m))
            ->all();
    }

    public function listFinishedToday(int $tenantId, int $branchId): array
    {
        return RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'FINISHED')
            ->whereDate('ended_at', Carbon::today())
            ->orderByDesc('ended_at')
            ->get()
            ->map(fn (RoomServiceModel $m) => GirlIncomeMapper::roomService($m))
            ->all();
    }

    public function findDueUnalerted(): array
    {
        return RoomServiceModel::query()
            ->where('status', 'ACTIVE')
            ->where('expected_ends_at', '<=', Carbon::now(config('app.timezone', 'America/La_Paz')))
            ->whereNull('alert_sent_at')
            ->get()
            ->map(fn (RoomServiceModel $m) => GirlIncomeMapper::roomService($m))
            ->all();
    }

    public function summarizeForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $row = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'FINISHED')
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
        ?int $roomId,
        ?string $roomNumber,
        ?string $roomLabel,
        string $unitPrice,
        string $totalAmount,
        string $girlPercent,
        string $grossGirlAmount,
        string $girlAmount,
        string $houseAmount,
        string $cleaningAmount,
        int $registeredByUserId,
        string $registeredAt,
        string $startedAt,
        int $durationMinutes,
        string $expectedEndsAt,
        ?string $notes,
        int $cashSessionId,
        string $paymentMethod,
    ): array {
        $model = RoomServiceModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_session_id' => $cashSessionId,
            'girl_user_id' => $girlUserId,
            'room_id' => $roomId,
            'room_number' => $roomNumber,
            'room_label' => $roomLabel ?? $roomNumber,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'girl_percent' => $girlPercent,
            'gross_girl_amount' => $grossGirlAmount,
            'girl_amount' => $girlAmount,
            'house_amount' => $houseAmount,
            'cleaning_amount' => $cleaningAmount,
            'payment_method' => $paymentMethod,
            'registered_by_user_id' => $registeredByUserId,
            'registered_at' => $registeredAt,
            'started_at' => $startedAt,
            'duration_minutes' => $durationMinutes,
            'expected_ends_at' => $expectedEndsAt,
            'status' => 'ACTIVE',
            'notes' => $notes,
        ]);

        return GirlIncomeMapper::roomService($model->fresh());
    }

    public function attachCashMovement(int $id, int $tenantId, int $cashMovementId): void
    {
        RoomServiceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->update(['cash_movement_id' => $cashMovementId]);
    }

    public function markAlertSent(int $roomServiceId): void
    {
        RoomServiceModel::query()
            ->whereKey($roomServiceId)
            ->update(['alert_sent_at' => Carbon::now()]);
    }

    public function markDue(int $roomServiceId): void
    {
        RoomServiceModel::query()
            ->whereKey($roomServiceId)
            ->where('status', 'ACTIVE')
            ->update(['status' => 'DUE']);
    }

    public function finish(int $id, int $tenantId, int $branchId, ?int $cleaningUserId): ?array
    {
        $model = RoomServiceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update([
            'status' => 'FINISHED',
            'ended_at' => Carbon::now(),
            'cleaning_user_id' => $cleaningUserId ?? $model->cleaning_user_id,
        ]);

        return GirlIncomeMapper::roomService($model->fresh());
    }

    public function check(int $id, int $tenantId, int $branchId, int $checkedByUserId): ?array
    {
        $model = RoomServiceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['ACTIVE', 'DUE', 'FINISHED'])
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update([
            'checked_by_user_id' => $checkedByUserId,
            'checked_at' => Carbon::now(),
        ]);

        return GirlIncomeMapper::roomService($model->fresh());
    }

    public function cancel(int $id, int $tenantId, int $branchId): ?array
    {
        $model = RoomServiceModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->first();

        if ($model === null) {
            return null;
        }

        $model->update([
            'status' => 'CANCELLED',
            'ended_at' => Carbon::now(),
        ]);

        return GirlIncomeMapper::roomService($model->fresh());
    }
}
