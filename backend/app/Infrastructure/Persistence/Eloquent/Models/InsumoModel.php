<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsumoModel extends Model
{
    protected $table = 'insumos';

    protected $fillable = [
        'tenant_id',
        'almacen_id',
        'codigo',
        'nombre',
        'unidad_medida',
        'costo_promedio',
        'ultimo_costo',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'activo',
    ];

    protected $casts = [
        'costo_promedio' => 'decimal:4',
        'ultimo_costo' => 'decimal:4',
        'stock_actual' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'stock_maximo' => 'decimal:3',
        'activo' => 'boolean',
    ];

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(AlmacenModel::class, 'almacen_id');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(RecetaModel::class, 'insumo_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(KardexMovimientoModel::class, 'insumo_id');
    }
}