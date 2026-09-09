<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProveedorModel extends Model
{
    protected $table = 'proveedores';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'razon_social',
        'nit',
        'telefono',
        'celular',
        'contacto',
        'direccion',
        'correo',
        'banco',
        'nro_cuenta',
        'titular_cuenta',
        'saldo_deuda',
        'activo',
    ];

    protected $casts = [
        'saldo_deuda' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function compras(): HasMany
    {
        return $this->hasMany(CompraModel::class, 'proveedor_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(ProveedorPagoModel::class, 'proveedor_id');
    }
}