<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashRegisterModel extends Model
{
    protected $table = 'cash_registers';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'code',
        'status',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
}
