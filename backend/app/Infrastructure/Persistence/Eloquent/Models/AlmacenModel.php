<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlmacenModel extends Model
{
    protected $table = 'almacenes';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'descripcion',
        'es_interno',
        'responsable',
        'activo',
    ];

    protected $casts = [
        'es_interno' => 'boolean',
        'activo' => 'boolean',
    ];

    public function insumos(): HasMany
    {
        return $this->hasMany(InsumoModel::class, 'almacen_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(CompraModel::class, 'almacen_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(KardexMovimientoModel::class, 'almacen_id');
    }
}