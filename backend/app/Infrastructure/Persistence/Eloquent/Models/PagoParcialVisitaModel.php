<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoParcialVisitaModel extends Model
{
    protected $table = 'pagos_parciales_visita';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'visita_id',
        'subcuenta_id',
        'turno_id',
        'cajero_id',
        'monto',
        'metodo_pago',
        'comprobante_nro',
        'notas',
    ];

    protected $casts = [
        'monto' => 'float',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function subcuenta(): BelongsTo
    {
        return $this->belongsTo(SubcuentaVisitaModel::class, 'subcuenta_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cajero_id');
    }
}
