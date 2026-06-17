<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ServiceTableModel extends Model
{
    protected $table = 'service_tables';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'service_area_id',
        'code',
        'label',
        'sort_order',
        'status',
    ];

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(ServiceAreaModel::class, 'service_area_id');
    }
}
