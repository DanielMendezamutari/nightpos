<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitaModel extends Model
{
    protected $table = 'visitas';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'mesa_id',
        'mesero_id',
        'cliente_id',
        'cliente_nombre',
        'personas',
        'estado',
        'imprimio_cuenta',
        'fecha_apertura',
        'fecha_cierre',
        'total',
        'notas',
    ];

    protected $casts = [
        'personas' => 'integer',
        'imprimio_cuenta' => 'boolean',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function mesa(): BelongsTo
    {
        return $this->belongsTo(MesaModel::class, 'mesa_id');
    }

    public function mesero(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'mesero_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VisitaDetalleModel::class, 'visita_id');
    }
}