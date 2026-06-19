<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJobModel extends Model
{
    protected $table = 'print_jobs';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'device_id',
        'type',
        'source_type',
        'source_id',
        'idempotency_key',
        'payload',
        'content_text',
        'status',
        'priority',
        'attempts',
        'max_attempts',
        'last_error',
        'requested_by_user_id',
        'claimed_at',
        'printed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'priority' => 'integer',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'claimed_at' => 'datetime',
            'printed_at' => 'datetime',
            'failed_at' => 'datetime',
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

    public function device(): BelongsTo
    {
        return $this->belongsTo(PrintDeviceModel::class, 'device_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'requested_by_user_id');
    }
}
