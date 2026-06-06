<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashMovementModel extends Model
{
    public $timestamps = false;

    protected $table = 'cash_movements';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'cash_session_id',
        'movement_type',
        'amount',
        'description',
        'payment_method',
        'cash_movement_reason_id',
        'notes',
        'source_type',
        'source_id',
        'created_by_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashSessionModel::class, 'cash_session_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(CashMovementReasonModel::class, 'cash_movement_reason_id');
    }
}
