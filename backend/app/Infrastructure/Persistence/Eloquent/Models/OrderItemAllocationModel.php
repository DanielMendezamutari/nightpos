<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItemAllocationModel extends Model
{
    protected $table = 'order_item_allocations';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'order_item_id',
        'girl_user_id',
        'units',
        'unit_amount',
        'total_amount',
        'allocation_type',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItemModel::class, 'order_item_id');
    }

    public function girl(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'girl_user_id');
    }
}
