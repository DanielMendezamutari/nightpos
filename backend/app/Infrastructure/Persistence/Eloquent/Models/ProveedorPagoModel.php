<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProveedorPagoModel extends Model
{
    protected $table = 'proveedor_pagos';

    protected $fillable = [
        'proveedor_id',
        'turno_id',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'metodo_pago',
        'referencia',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'saldo_anterior' => 'decimal:2',
        'saldo_nuevo' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorModel::class, 'proveedor_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }
}