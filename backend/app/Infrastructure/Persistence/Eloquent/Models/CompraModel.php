<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompraModel extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'tenant_id',
        'proveedor_id',
        'almacen_id',
        'turno_id',
        'nro_factura',
        'fecha_compra',
        'monto_total',
        'descuento',
        'ice',
        'metodo_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_compra' => 'datetime',
        'monto_total' => 'decimal:2',
        'descuento' => 'decimal:2',
        'ice' => 'decimal:2',
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(ProveedorModel::class, 'proveedor_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(AlmacenModel::class, 'almacen_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalleModel::class, 'compra_id');
    }
}