<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WaiterTableAssignmentModel extends Model
{
    protected $table = 'waiter_table_assignments';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'waiter_user_id',
        'service_table_id',
        'official_shift_id',
        'assigned_by_user_id',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }

    public function serviceTable(): BelongsTo
    {
        return $this->belongsTo(ServiceTableModel::class, 'service_table_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'waiter_user_id');
    }
}
