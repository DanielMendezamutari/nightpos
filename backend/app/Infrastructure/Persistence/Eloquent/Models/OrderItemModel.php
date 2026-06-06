<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItemModel extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'order_id',
        'product_id',
        'product_name',
        'sale_mode',
        'quantity',
        'unit_price',
        'line_total',
        'girl_amount',
        'house_amount',
        'girl_user_id',
        'item_status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'girl_amount' => 'decimal:2',
            'house_amount' => 'decimal:2',
            'cancelled_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
