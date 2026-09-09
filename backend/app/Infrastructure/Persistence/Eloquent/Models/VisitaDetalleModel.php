<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitaDetalleModel extends Model
{
    protected $table = 'visita_detalles';

    protected $fillable = [
        'visita_id',
        'producto_id',
        'producto_nombre',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }
}