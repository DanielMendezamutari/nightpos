<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaModel extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'tenant_id',
        'codigo',
        'nombre',
        'icono',
        'color',
        'orden',
        'activo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(ProductoModel::class, 'categoria_id');
    }
}