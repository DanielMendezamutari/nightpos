<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoModel extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'turno_id',
        'tipo_gasto_id',
        'usuario_id',
        'beneficiario',
        'monto',
        'forma_pago',
        'comprobante_nro',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function tipoGasto(): BelongsTo
    {
        return $this->belongsTo(TipoGastoModel::class, 'tipo_gasto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }
}
