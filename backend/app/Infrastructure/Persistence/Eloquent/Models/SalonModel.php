<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalonModel extends Model
{
    protected $table = 'salones';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'codigo',
        'nombre',
        'impresora_cuenta',
        'impresora_factura',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function mesas(): HasMany
    {
        return $this->hasMany(MesaModel::class, 'salon_id');
    }
}