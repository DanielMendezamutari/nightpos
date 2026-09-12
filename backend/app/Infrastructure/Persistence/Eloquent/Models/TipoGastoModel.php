<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoGastoModel extends Model
{
    protected $table = 'tipos_gastos';

    protected $fillable = [
        'tenant_id',
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function gastos(): HasMany
    {
        return $this->hasMany(GastoModel::class, 'tipo_gasto_id');
    }
}
