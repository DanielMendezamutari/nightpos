<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomModel extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'code',
        'name',
        'room_type',
        'status',
        'default_duration_minutes',
        'suggested_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'default_duration_minutes' => 'integer',
            'suggested_price' => 'decimal:2',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function roomServices(): HasMany
    {
        return $this->hasMany(RoomServiceModel::class, 'room_id');
    }
}
