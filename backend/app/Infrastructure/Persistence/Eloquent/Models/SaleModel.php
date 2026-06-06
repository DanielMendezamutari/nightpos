<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SaleModel extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'official_shift_id',
        'cash_session_id',
        'order_id',
        'sale_number',
        'cashier_user_id',
        'waiter_user_id',
        'subtotal',
        'total',
        'currency',
        'payment_mode',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItemModel::class, 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePaymentModel::class, 'sale_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cashier_user_id');
    }
}
