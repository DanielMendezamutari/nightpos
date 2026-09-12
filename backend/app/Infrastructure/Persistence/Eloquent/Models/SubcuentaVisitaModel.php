<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubcuentaVisitaModel extends Model
{
    protected $table = 'subcuentas_visita';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'visita_id',
        'numero_subcuenta',
        'nombre_comensal',
        'estado',
        'total',
        'monto_pagado',
        'factura_id',
    ];

    protected $casts = [
        'numero_subcuenta' => 'integer',
        'total' => 'float',
        'monto_pagado' => 'float',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VisitaDetalleModel::class, 'subcuenta_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaModel::class, 'factura_id');
    }

    public function pagosParciales(): HasMany
    {
        return $this->hasMany(PagoParcialVisitaModel::class, 'subcuenta_id');
    }
}
