<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'order_number',
        'status',
        'table_label',
        'service_area_id',
        'waiter_user_id',
        'opened_by_user_id',
        'notes',
        'subtotal',
        'total',
        'currency',
        'sent_to_bar_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'sent_to_bar_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'waiter_user_id');
    }
}
