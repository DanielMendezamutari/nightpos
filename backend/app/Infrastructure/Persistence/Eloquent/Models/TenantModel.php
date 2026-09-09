<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantModel extends Model
{
    use HasUuids;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'company_brand',
        'is_active',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(BranchModel::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'tenant_id');
    }
}
