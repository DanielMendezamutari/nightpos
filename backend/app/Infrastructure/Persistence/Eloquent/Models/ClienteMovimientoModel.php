<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteMovimientoModel extends Model
{
    protected $table = 'cliente_movimientos';

    protected $fillable = [
        'cliente_id',
        'turno_id',
        'tipo',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'visita_id',
        'factura_id',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(ClienteModel::class, 'cliente_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaModel::class, 'factura_id');
    }
}