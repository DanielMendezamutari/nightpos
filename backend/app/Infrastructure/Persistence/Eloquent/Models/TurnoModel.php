<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoModel extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'cajero_id',
        'fecha_inicio',
        'fecha_fin',
        'monto_inicial_bs',
        'monto_inicial_usd',
        'monto_final_bs',
        'monto_final_usd',
        'total_ventas_efectivo',
        'total_ventas_qr',
        'total_ventas_tarjeta',
        'total_gastos',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'monto_inicial_bs' => 'float',
        'monto_inicial_usd' => 'float',
        'monto_final_bs' => 'float',
        'monto_final_usd' => 'float',
        'total_ventas_efectivo' => 'float',
        'total_ventas_qr' => 'float',
        'total_ventas_tarjeta' => 'float',
        'total_gastos' => 'float',
        'diferencia' => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'cajero_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCajaModel::class, 'turno_id');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(FacturaModel::class, 'turno_id');
    }
}