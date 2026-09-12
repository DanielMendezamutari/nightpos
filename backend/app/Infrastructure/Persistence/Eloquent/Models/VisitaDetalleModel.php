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
        'subcuenta_id',
        'producto_id',
        'producto_nombre',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'observaciones',
        'estacion_cocina',
        'estado',
        'iniciado_at',
        'terminado_at',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iniciado_at' => 'datetime',
        'terminado_at' => 'datetime',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(ProductoModel::class, 'producto_id');
    }

    public function subcuenta(): BelongsTo
    {
        return $this->belongsTo(SubcuentaVisitaModel::class, 'subcuenta_id');
    }
}
