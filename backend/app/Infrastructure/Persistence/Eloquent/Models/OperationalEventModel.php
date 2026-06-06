<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class OperationalEventModel extends Model
{
    protected $table = 'operational_events';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'type',
        'target_role',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}
