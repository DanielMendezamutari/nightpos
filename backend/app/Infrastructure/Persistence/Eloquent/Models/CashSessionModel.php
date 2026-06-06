<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CashSessionModel extends Model
{
    protected $table = 'cash_sessions';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'cash_register_id',
        'opened_by_user_id',
        'closed_by_user_id',
        'status',
        'opening_amount',
        'expected_amount',
        'declared_closing_amount',
        'difference_amount',
        'opening_notes',
        'closing_notes',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'declared_closing_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovementModel::class, 'cash_session_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'opened_by_user_id');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'closed_by_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function officialShift(): BelongsTo
    {
        return $this->belongsTo(OfficialShiftModel::class, 'official_shift_id');
    }
}
