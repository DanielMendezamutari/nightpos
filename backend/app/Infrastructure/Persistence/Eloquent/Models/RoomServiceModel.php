<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomServiceModel extends Model
{
    protected $table = 'room_services';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'cash_session_id',
        'girl_user_id',
        'room_id',
        'room_number',
        'room_label',
        'unit_price',
        'total_amount',
        'girl_percent',
        'gross_girl_amount',
        'girl_amount',
        'house_amount',
        'cleaning_amount',
        'payment_method',
        'cash_movement_id',
        'registered_by_user_id',
        'registered_at',
        'started_at',
        'duration_minutes',
        'expected_ends_at',
        'ended_at',
        'status',
        'cleaning_user_id',
        'checked_by_user_id',
        'checked_at',
        'alert_sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'girl_percent' => 'decimal:2',
            'gross_girl_amount' => 'decimal:2',
            'girl_amount' => 'decimal:2',
            'house_amount' => 'decimal:2',
            'cleaning_amount' => 'decimal:2',
            'registered_at' => 'datetime',
            'started_at' => 'datetime',
            'expected_ends_at' => 'datetime',
            'ended_at' => 'datetime',
            'checked_at' => 'datetime',
            'alert_sent_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'checked_by_user_id');
    }

    public function cleaningUser(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cleaning_user_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }

    public function girl(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'girl_user_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'registered_by_user_id');
    }

    public function officialShift(): BelongsTo
    {
        return $this->belongsTo(OfficialShiftModel::class, 'official_shift_id');
    }
}
