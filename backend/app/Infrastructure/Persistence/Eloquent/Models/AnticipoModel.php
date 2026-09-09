<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnticipoModel extends Model
{
    protected $table = 'anticipos';

    protected $fillable = [
        'cliente_id',
        'turno_id',
        'visita_id',
        'monto_inicial',
        'saldo_disponible',
        'estado',
        'concepto',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'saldo_disponible' => 'decimal:2',
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
}