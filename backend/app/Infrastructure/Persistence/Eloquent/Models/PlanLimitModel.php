<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanLimitModel extends Model
{
    protected $table = 'plan_limits';

    protected $fillable = [
        'plan_id',
        'limit_key',
        'limit_value',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanModel::class, 'plan_id');
    }
}
