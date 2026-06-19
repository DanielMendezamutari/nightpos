<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintDeviceModel extends Model
{
    protected $table = 'print_devices';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'device_key_hash',
        'device_key_prefix',
        'status',
        'enabled',
        'printer_name',
        'paper_width_mm',
        'auto_print_order',
        'last_seen_at',
        'last_error',
        'agent_version',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'auto_print_order' => 'boolean',
            'last_seen_at' => 'datetime',
            'paper_width_mm' => 'integer',
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

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJobModel::class, 'device_id');
    }
}
