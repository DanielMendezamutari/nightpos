<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantModel extends Model
{
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'plan_id',
        'plan_name',
        'subscription_starts_at',
        'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'subscription_starts_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanModel::class, 'plan_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(BranchModel::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(UserModel::class, 'tenant_id');
    }
}
