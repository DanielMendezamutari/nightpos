<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteModel extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'apellidos',
        'ci_nit',
        'tipo_documento',
        'razon_social',
        'celular',
        'telefono',
        'correo',
        'direccion',
        'cumpleanos',
        'descuento_porcentaje',
        'limite_credito',
        'saldo_deuda',
        'permite_credito',
        'comentarios',
        'activo',
    ];

    protected $casts = [
        'descuento_porcentaje' => 'decimal:2',
        'limite_credito' => 'decimal:2',
        'saldo_deuda' => 'decimal:2',
        'permite_credito' => 'boolean',
        'activo' => 'boolean',
        'cumpleanos' => 'date',
    ];

    public function movimientos(): HasMany
    {
        return $this->hasMany(ClienteMovimientoModel::class, 'cliente_id')->latest();
    }

    public function anticipos(): HasMany
    {
        return $this->hasMany(AnticipoModel::class, 'cliente_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . ($this->apellidos ?? ''));
    }
}