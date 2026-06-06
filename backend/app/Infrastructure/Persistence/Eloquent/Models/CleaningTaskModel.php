<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningTaskModel extends Model
{
    protected $table = 'cleaning_tasks';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'room_id',
        'room_service_id',
        'cleaning_user_id',
        'amount',
        'status',
        'cleaned_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'cleaned_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(RoomModel::class, 'room_id');
    }

    public function roomService(): BelongsTo
    {
        return $this->belongsTo(RoomServiceModel::class, 'room_service_id');
    }

    public function cleaningUser(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cleaning_user_id');
    }
}
