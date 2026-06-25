<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StaffSettlementAdjustmentModel extends Model
{
    protected $table = 'staff_settlement_adjustments';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'staff_settlement_id',
        'staff_fine_id',
        'adjustment_type',
        'amount',
        'discount_mode',
        'discount_value',
        'calculation_base',
        'notes',
        'dedup_key',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'calculation_base' => 'decimal:2',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(StaffSettlementModel::class, 'staff_settlement_id');
    }
}
