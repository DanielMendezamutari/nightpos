<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ShowTypeModel extends Model
{
    protected $table = 'show_types';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'suggested_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'suggested_price' => 'decimal:2',
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
