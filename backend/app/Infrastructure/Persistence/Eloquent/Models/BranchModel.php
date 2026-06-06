<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchModel extends Model
{
    protected $table = 'branches';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'address',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'branch_id');
    }
}
