<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleItemAllocationModel extends Model
{
    protected $table = 'sale_item_allocations';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'sale_item_id',
        'girl_user_id',
        'units',
        'unit_amount_snapshot',
        'total_amount_snapshot',
        'source_order_item_allocation_id',
        'allocation_type',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount_snapshot' => 'decimal:2',
            'total_amount_snapshot' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItemModel::class, 'sale_item_id');
    }

    public function girl(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'girl_user_id');
    }
}
