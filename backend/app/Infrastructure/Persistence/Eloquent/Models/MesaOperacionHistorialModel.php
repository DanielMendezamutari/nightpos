<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MesaOperacionHistorialModel extends Model
{
    protected $table = 'mesa_operaciones_historial';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'tipo_operacion',
        'visita_id',
        'mesa_origen_id',
        'mesa_destino_id',
        'usuario_id',
        'motivo',
        'detalles_json',
    ];

    protected $casts = [
        'detalles_json' => 'array',
    ];

    public function visita(): BelongsTo
    {
        return $this->belongsTo(VisitaModel::class, 'visita_id');
    }

    public function mesaOrigen(): BelongsTo
    {
        return $this->belongsTo(MesaModel::class, 'mesa_origen_id');
    }

    public function mesaDestino(): BelongsTo
    {
        return $this->belongsTo(MesaModel::class, 'mesa_destino_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }
}
