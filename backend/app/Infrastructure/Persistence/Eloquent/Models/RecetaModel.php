<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecetaModel extends Model
{
    protected $table = 'recetas';

    protected $fillable = [
        'producto_id',
        'insumo_id',
        'cantidad',
        'unidad_medida',
        'merma_porcentaje',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'merma_porcentaje' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }

    public function insumo(): BelongsTo
    {
        return $this->belongsTo(InsumoModel::class, 'insumo_id');
    }
}