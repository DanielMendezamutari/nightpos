<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanModel extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'code',
        'description',
        'monthly_price',
        'yearly_price',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function limits(): HasMany
    {
        return $this->hasMany(PlanLimitModel::class, 'plan_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(TenantModel::class, 'plan_id');
    }
}
