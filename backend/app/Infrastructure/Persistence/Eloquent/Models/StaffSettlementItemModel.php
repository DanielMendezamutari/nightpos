<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StaffSettlementItemModel extends Model
{
    protected $table = 'staff_settlement_items';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'staff_settlement_id',
        'sale_id',
        'sale_item_id',
        'order_id',
        'source_id',
        'source_type',
        'description',
        'base_amount',
        'percent',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'percent' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(StaffSettlementModel::class, 'staff_settlement_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItemModel::class, 'sale_item_id');
    }
}
