<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropinaModel extends Model
{
    protected $table = 'propinas';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'visita_id',
        'subcuenta_id',
        'factura_id',
        'mesero_id',
        'turno_id',
        'monto_propina',
        'porcentaje',
        'metodo_pago',
    ];

    protected $casts = [
        'monto_propina' => 'float',
        'porcentaje' => 'float',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function subcuenta(): BelongsTo
    {
        return $this->belongsTo(SubcuentaVisitaModel::class, 'subcuenta_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'mesero_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(FacturaModel::class, 'factura_id');
    }
}
