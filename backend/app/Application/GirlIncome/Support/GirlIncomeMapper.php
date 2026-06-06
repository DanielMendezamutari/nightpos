<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\Support;

use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Entities\OfficialShift;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class GirlIncomeMapper
{
    public static function shift(?OfficialShift $shift): ?array
    {
        return $shift ? ShiftMapper::shift($shift) : null;
    }

    public static function bracelet(BraceletModel $model): array
    {
        return self::mapEntry($model, [
            'quantity' => (int) $model->quantity,
            'unit_price' => self::money($model->unit_price),
            'total_amount' => self::money($model->total_amount),
        ]);
    }

    public static function roomService(RoomServiceModel $model): array
    {
        $now = Carbon::now(config('app.timezone', 'America/La_Paz'));
        $expected = $model->expected_ends_at;
        $remainingMinutes = null;
        $isDue = false;

        if ($model->status === 'DUE') {
            $isDue = true;
            $remainingMinutes = 0;
        } elseif ($model->status === 'ACTIVE' && $expected !== null) {
            $isDue = $expected->lte($now);
            $remainingMinutes = $isDue ? 0 : (int) $now->diffInMinutes($expected);
        }

        return self::mapEntry($model, [
            'room_id' => $model->room_id,
            'room_number' => $model->room_number,
            'room_label' => $model->room_label ?? $model->room_number,
            'unit_price' => self::money($model->unit_price),
            'total_amount' => self::money($model->total_amount),
            'girl_percent' => $model->girl_percent !== null
                ? number_format((float) $model->girl_percent, 2, '.', '')
                : null,
            'gross_girl_amount' => self::money($model->gross_girl_amount ?? $model->girl_amount ?? $model->total_amount),
            'girl_amount' => self::money($model->girl_amount ?? $model->total_amount),
            'cleaning_amount' => self::money($model->cleaning_amount ?? 0),
            'house_amount' => self::money($model->house_amount ?? 0),
            'started_at' => $model->started_at?->format('Y-m-d H:i:s'),
            'duration_minutes' => (int) ($model->duration_minutes ?? 0),
            'expected_ends_at' => $expected?->format('Y-m-d H:i:s'),
            'ended_at' => $model->ended_at?->format('Y-m-d H:i:s'),
            'status' => $model->status,
            'status_label' => self::roomStatusLabel($model->status),
            'remaining_minutes' => $remainingMinutes,
            'is_due' => $isDue,
            'checked_at' => $model->checked_at?->format('Y-m-d H:i:s'),
            'checked_by_user_id' => $model->checked_by_user_id,
            'checked_by_name' => $model->relationLoaded('checkedBy') ? $model->checkedBy?->name : null,
            'alert_sent_at' => $model->alert_sent_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public static function roomStatusLabel(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'ACTIVE' => 'Activa',
            'DUE' => 'Tiempo cumplido',
            'FINISHED' => 'Terminada',
            'CANCELLED' => 'Cancelada',
            default => (string) $status,
        };
    }

    public static function show(ShowModel $model): array
    {
        return self::mapEntry($model, [
            'show_type' => $model->show_type,
            'show_type_label' => self::showTypeLabel($model->show_type),
            'unit_price' => self::money($model->unit_price),
            'total_amount' => self::money($model->total_amount),
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function mapEntry(Model $model, array $extra): array
    {
        $table = $model->getTable();
        $relations = ['girl', 'registeredBy', 'officialShift'];

        if ($table === 'bracelets') {
            $relations[] = 'waiter';
        }

        if ($table === 'room_services') {
            $relations[] = 'checkedBy';
        }

        $model->loadMissing($relations);

        $base = [
            'id' => $model->id,
            'tenant_id' => $model->tenant_id,
            'branch_id' => $model->branch_id,
            'official_shift_id' => $model->official_shift_id,
            'girl_user_id' => $model->girl_user_id,
            'girl_name' => $model->girl?->name,
            'registered_by_user_id' => $model->registered_by_user_id,
            'registered_by_name' => $model->registeredBy?->name,
            'registered_at' => $model->registered_at?->format('Y-m-d H:i:s'),
            'notes' => $model->notes,
            'shift' => $model->officialShift ? [
                'id' => $model->officialShift->id,
                'name' => $model->officialShift->name,
                'shift_type' => $model->officialShift->shift_type,
                'business_date' => $model->officialShift->business_date?->format('Y-m-d'),
            ] : null,
            'settlement_source_type' => match ($table) {
                'bracelets' => 'GIRL_BRACELET',
                'room_services' => 'GIRL_ROOM',
                'shows' => 'GIRL_SHOW',
                default => null,
            },
        ];

        if ($table === 'bracelets') {
            $base['waiter_user_id'] = $model->waiter_user_id;
            $base['waiter_name'] = $model->waiter?->name;
        }

        return array_merge($base, $extra);
    }

    public static function showTypeLabel(string $type): string
    {
        return match (strtoupper($type)) {
            'PRIVATE' => 'Privado',
            'STAGE' => 'Escenario',
            'SPECIAL' => 'Especial',
            default => $type,
        };
    }

    private static function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
