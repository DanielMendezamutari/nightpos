<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MesaModel extends Model
{
    protected $table = 'mesas';

    protected $fillable = [
        'salon_id',
        'codigo',
        'nombre',
        'capacidad',
        'posicion_x',
        'posicion_y',
        'ancho',
        'alto',
        'forma',
        'activo',
    ];

    protected $casts = [
        'capacidad' => 'integer',
        'posicion_x' => 'integer',
        'posicion_y' => 'integer',
        'ancho' => 'integer',
        'alto' => 'integer',
        'activo' => 'boolean',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(SalonModel::class, 'salon_id');
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(VisitaModel::class, 'mesa_id');
    }

    public function visitaActiva(): HasOne
    {
        return $this->hasOne(VisitaModel::class, 'mesa_id')
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->latestOfMany();
    }
}