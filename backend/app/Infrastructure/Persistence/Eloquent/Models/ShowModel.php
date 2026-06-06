<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowModel extends Model
{
    protected $table = 'shows';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'cash_session_id',
        'girl_user_id',
        'show_type',
        'unit_price',
        'total_amount',
        'payment_method',
        'cash_movement_id',
        'registered_by_user_id',
        'registered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'registered_at' => 'datetime',
        ];
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
