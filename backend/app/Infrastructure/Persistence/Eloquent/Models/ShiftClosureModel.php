<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ShiftClosureModel extends Model
{
    protected $table = 'shift_closures';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'total_cash',
        'total_qr',
        'total_card',
        'total_sales',
        'total_manual_income',
        'total_manual_expense',
        'total_girl_payouts',
        'total_waiter_payouts',
        'expected_cash',
        'counted_cash',
        'cash_difference',
        'status',
        'closed_by_user_id',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    public function officialShift(): BelongsTo
    {
        return $this->belongsTo(OfficialShiftModel::class, 'official_shift_id');
    }
}
