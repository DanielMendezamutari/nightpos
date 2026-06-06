<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class StaffSettlementModel extends Model
{
    protected $table = 'staff_settlements';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'cash_session_id',
        'staff_user_id',
        'staff_role',
        'settlement_type',
        'total_amount',
        'status',
        'paid_by_user_id',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'staff_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StaffSettlementItemModel::class, 'staff_settlement_id');
    }

    public function officialShift(): BelongsTo
    {
        return $this->belongsTo(OfficialShiftModel::class, 'official_shift_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'paid_by_user_id');
    }
}
