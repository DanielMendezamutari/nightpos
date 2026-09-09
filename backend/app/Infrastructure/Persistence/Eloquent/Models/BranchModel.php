<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchModel extends Model
{
    use HasUuids;

    protected $table = 'branches';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'address',
        'phone',
        'nit',
        'municipality',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }
}
