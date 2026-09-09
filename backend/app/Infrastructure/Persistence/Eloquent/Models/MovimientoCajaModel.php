<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCajaModel extends Model
{
    protected $table = 'movimientos_caja';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'turno_id',
        'usuario_id',
        'tipo',
        'monto',
        'motivo',
        'comprobante_nro',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'float',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }
}