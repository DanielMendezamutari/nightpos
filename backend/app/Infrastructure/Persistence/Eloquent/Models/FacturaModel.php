<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaModel extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'visita_id',
        'turno_id',
        'cajero_id',
        'nro_factura',
        'cuf',
        'cufd',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'correo',
        'metodo_pago',
        'monto_total',
        'monto_efectivo',
        'monto_cambio',
        'estado',
        'codigo_siat',
        'fecha_emision',
    ];

    protected $casts = [
        'nro_factura' => 'integer',
        'monto_total' => 'float',
        'monto_efectivo' => 'float',
        'monto_cambio' => 'float',
        'fecha_emision' => 'datetime',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cajero_id');
    }

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }
}