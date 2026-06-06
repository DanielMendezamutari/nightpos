<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class OfficialShiftModel extends Model
{
    protected $table = 'official_shifts';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'shift_type',
        'business_date',
        'starts_at',
        'ends_at',
        'status',
        'opened_by_user_id',
        'closed_by_user_id',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'opened_by_user_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'closed_by_user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function closure(): HasOne
    {
        return $this->hasOne(ShiftClosureModel::class, 'official_shift_id');
    }
}
