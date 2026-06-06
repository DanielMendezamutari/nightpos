<?php

declare(strict_types=1);

namespace App\Application\Room\Support;

use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;

final class RoomMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function room(RoomModel $model, array $extra = []): array
    {
        $model->loadMissing('branch');

        return array_merge([
            'id' => $model->id,
            'tenant_id' => $model->tenant_id,
            'branch_id' => $model->branch_id,
            'branch_code' => $model->branch?->code,
            'branch_name' => $model->branch?->name,
            'code' => $model->code,
            'name' => $model->name,
            'room_type' => $model->room_type,
            'room_type_label' => self::typeLabel($model->room_type),
            'status' => $model->status,
            'status_label' => self::statusLabel($model->status),
            'default_duration_minutes' => $model->default_duration_minutes !== null
                ? (int) $model->default_duration_minutes
                : null,
            'suggested_price' => $model->suggested_price !== null
                ? number_format((float) $model->suggested_price, 2, '.', '')
                : null,
            'notes' => $model->notes,
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),
        ], $extra);
    }

    public static function typeLabel(string $type): string
    {
        return match (strtoupper($type)) {
            'STANDARD' => 'Estándar',
            'VIP' => 'VIP',
            'SUITE' => 'Suite',
            default => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match (strtoupper($status)) {
            'AVAILABLE' => 'Disponible',
            'OCCUPIED' => 'Ocupada',
            'CLEANING' => 'Limpieza',
            'MAINTENANCE' => 'Mantenimiento',
            default => $status,
        };
    }
}
