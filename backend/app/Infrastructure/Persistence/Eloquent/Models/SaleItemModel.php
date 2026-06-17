<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SaleItemModel extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'sale_id',
        'order_item_id',
        'product_id',
        'product_name_snapshot',
        'sale_mode',
        'quantity',
        'unit_price_snapshot',
        'line_total',
        'girl_user_id',
        'girl_amount_snapshot',
        'house_amount_snapshot',
        'waiter_commission_percent_snapshot',
        'waiter_commission_amount_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_snapshot' => 'decimal:2',
            'line_total' => 'decimal:2',
            'girl_amount_snapshot' => 'decimal:2',
            'house_amount_snapshot' => 'decimal:2',
            'waiter_commission_percent_snapshot' => 'decimal:2',
            'waiter_commission_amount_snapshot' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(SaleModel::class, 'sale_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SaleItemAllocationModel::class, 'sale_item_id');
    }
}
