<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoQrModel extends Model
{
    protected $table = 'pagos_qr';

    protected $fillable = [
        'visita_id',
        'mesa_id',
        'codigo_transaccion',
        'monto',
        'moneda',
        'glosa',
        'estado',
        'qr_payload',
        'banco',
        'referencia_bancaria',
        'vence_at',
        'pagado_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'vence_at' => 'datetime',
        'pagado_at' => 'datetime',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }
}
