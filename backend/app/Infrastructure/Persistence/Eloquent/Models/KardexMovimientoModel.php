<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KardexMovimientoModel extends Model
{
    protected $table = 'kardex_movimientos';

    protected $fillable = [
        'tenant_id',
        'insumo_id',
        'almacen_id',
        'tipo',
        'cantidad',
        'costo_unitario',
        'stock_anterior',
        'stock_nuevo',
        'referencia',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
        'stock_anterior' => 'decimal:3',
        'stock_nuevo' => 'decimal:3',
    ];

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(InsumoModel::class, 'insumo_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(AlmacenModel::class, 'almacen_id');
    }
}