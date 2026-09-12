<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoModel extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'tenant_id',
        'categoria_id',
        'codigo',
        'nombre',
        'descripcion',
        'facturable',
        'precio',
        'costo',
        'tiempo_preparacion',
        'destino_impresion',
        'estacion_cocina',
        'imagen',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'costo' => 'decimal:2',
        'tiempo_preparacion' => 'integer',
        'facturable' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaModel::class, 'categoria_id');
    }
}