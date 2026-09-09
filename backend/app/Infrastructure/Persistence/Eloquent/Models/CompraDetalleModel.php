<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraDetalleModel extends Model
{
    protected $table = 'compra_detalles';

    protected $fillable = [
        'compra_id',
        'insumo_id',
        'cantidad',
        'costo_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
        'subtotal' => 'decimal:2',
    ];

    public function compra(): BelongsTo
    {
        return $this->belongsTo(CompraModel::class, 'compra_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(InsumoModel::class, 'insumo_id');
    }
}