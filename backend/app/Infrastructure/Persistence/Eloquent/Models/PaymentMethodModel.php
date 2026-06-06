<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentMethodModel extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'code',
        'name',
        'type',
        'enabled',
        'requires_reference',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'requires_reference' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
}
