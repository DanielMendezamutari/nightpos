<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantTechnicalProfileModel extends Model
{
    protected $table = 'tenant_technical_profiles';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'primary_pc_name',
        'operating_system',
        'ram',
        'printer_model',
        'printer_connection_type',
        'remote_support_tool',
        'remote_support_id',
        'installer_name',
        'installed_at',
        'installation_notes',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
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
}
