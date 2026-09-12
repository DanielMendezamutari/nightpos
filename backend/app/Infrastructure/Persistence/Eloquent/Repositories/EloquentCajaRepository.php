<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Caja\Entities\Turno;
use App\Domain\Caja\Entities\MovimientoCaja;
use App\Domain\Caja\Repositories\CajaRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoCajaModel;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;

class EloquentCajaRepository implements CajaRepositoryInterface
{
    public function getTurnoActivo(string $tenantId, string $branchId): ?Turno
    {
        $model = TurnoModel::with('cajero')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('estado', 'ABIERTO')
            ->latest('id')
            ->first();

        return $model ? $this->toTurnoDomain($model) : null;
    }

    public function getTurnoById(int $id): ?Turno
    {
        $model = TurnoModel::with('cajero')->find($id);
        return $model ? $this->toTurnoDomain($model) : null;
    }

    public function abrirTurno(
        string $tenantId,
        string $branchId,
        string $cajeroId,
        float $montoInicialBs,
        float $montoInicialUsd,
        ?string $observaciones = null
    ): Turno {
        $model = TurnoModel::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'cajero_id' => $cajeroId,
            'fecha_inicio' => now(),
            'monto_inicial_bs' => $montoInicialBs,
            'monto_inicial_usd' => $montoInicialUsd,
            'total_ventas_efectivo' => 0.0,
            'total_ventas_qr' => 0.0,
            'total_ventas_tarjeta' => 0.0,
            'total_gastos' => 0.0,
            'diferencia' => 0.0,
            'estado' => 'ABIERTO',
            'observaciones' => $observaciones,
        ]);

        return $this->toTurnoDomain($model->load('cajero'));
    }

    public function cerrarTurno(
        int $turnoId,
        float $montoFinalBs,
        float $montoFinalUsd,
        ?string $observaciones = null
    ): Turno {
        $model = TurnoModel::with('cajero')->findOrFail($turnoId);

        $efectivoEsperado = $model->monto_inicial_bs + $model->total_ventas_efectivo - $model->total_gastos;
        $diferencia = round($montoFinalBs - $efectivoEsperado, 2);

        $model->update([
            'fecha_fin' => now(),
            'monto_final_bs' => $montoFinalBs,
            'monto_final_usd' => $montoFinalUsd,
            'diferencia' => $diferencia,
            'estado' => 'CERRADO',
            'observaciones' => $observaciones ? ($model->observaciones ? $model->observaciones . " | " . $observaciones : $observaciones) : $model->observaciones,
        ]);

        return $this->toTurnoDomain($model->fresh('cajero'));
    }

    public function registrarMovimiento(
        string $tenantId,
        string $branchId,
        int $turnoId,
        string $usuarioId,
        string $tipo,
        float $monto,
        string $motivo,
        ?string $comprobanteNro = null,
        ?string $observaciones = null
    ): MovimientoCaja {
        $model = MovimientoCajaModel::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'turno_id' => $turnoId,
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'monto' => $monto,
            'motivo' => $motivo,
            'comprobante_nro' => $comprobanteNro,
            'observaciones' => $observaciones,
        ]);

        $this->recalcularTotalesTurno($turnoId);

        return $this->toMovimientoDomain($model->load('usuario'));
    }

    public function getMovimientosByTurno(int $turnoId): array
    {
        $models = MovimientoCajaModel::with('usuario')
            ->where('turno_id', $turnoId)
            ->latest('id')
            ->get();

        return $models->map(fn($m) => $this->toMovimientoDomain($m))->all();
    }

    public function recalcularTotalesTurno(int $turnoId): Turno
    {
        $turno = TurnoModel::findOrFail($turnoId);

        $gastos = MovimientoCajaModel::where('turno_id', $turnoId)
            ->whereIn('tipo', ['EGRESO_GASTO', 'RETIRO_CAJA'])
            ->sum('monto');

        $ingresosExtra = MovimientoCajaModel::where('turno_id', $turnoId)
            ->where('tipo', 'INGRESO')
            ->sum('monto');

        $ventasEfectivo = (float)(FacturaModel::where('turno_id', $turnoId)
            ->where('estado', 'VALIDA')
            ->selectRaw("SUM(CASE WHEN metodo_pago = 'EFECTIVO' THEN monto_total WHEN metodo_pago = 'MIXTO' THEN COALESCE(monto_efectivo, 0) ELSE 0 END) as total")
            ->value('total') ?? 0);

        $ventasQr = (float)(FacturaModel::where('turno_id', $turnoId)
            ->where('estado', 'VALIDA')
            ->selectRaw("SUM(CASE WHEN metodo_pago = 'QR' THEN monto_total WHEN metodo_pago = 'MIXTO' THEN COALESCE(monto_qr, 0) ELSE 0 END) as total")
            ->value('total') ?? 0);

        $ventasTarjeta = (float)(FacturaModel::where('turno_id', $turnoId)
            ->where('estado', 'VALIDA')
            ->selectRaw("SUM(CASE WHEN metodo_pago = 'TARJETA' THEN monto_total WHEN metodo_pago = 'MIXTO' THEN COALESCE(monto_tarjeta, 0) ELSE 0 END) as total")
            ->value('total') ?? 0);

        $totalFacturado = FacturaModel::where('turno_id', $turnoId)
            ->where('estado', 'VALIDA')
            ->where('tipo_comprobante', 'FACTURA')
            ->sum('monto_total');

        $totalRecibos = FacturaModel::where('turno_id', $turnoId)
            ->where('estado', 'VALIDA')
            ->where('tipo_comprobante', 'RECIBO')
            ->sum('monto_total');

        $turno->update([
            'total_ventas_efectivo' => round((float)$ventasEfectivo + (float)$ingresosExtra, 2),
            'total_ventas_qr' => round((float)$ventasQr, 2),
            'total_ventas_tarjeta' => round((float)$ventasTarjeta, 2),
            'total_facturado' => round((float)$totalFacturado, 2),
            'total_recibos' => round((float)$totalRecibos, 2),
            'total_gastos' => round((float)$gastos, 2),
        ]);

        return $this->toTurnoDomain($turno->fresh('cajero'));
    }

    private function toTurnoDomain(TurnoModel $model): Turno
    {
        return new Turno(
            id: (int)$model->id,
            tenantId: (string)$model->tenant_id,
            branchId: (string)$model->branch_id,
            cajeroId: (string)$model->cajero_id,
            fechaInicio: $model->fecha_inicio ? $model->fecha_inicio->toDateTimeString() : '',
            fechaFin: $model->fecha_fin ? $model->fecha_fin->toDateTimeString() : null,
            montoInicialBs: (float)$model->monto_inicial_bs,
            montoInicialUsd: (float)$model->monto_inicial_usd,
            montoFinalBs: $model->monto_final_bs !== null ? (float)$model->monto_final_bs : null,
            montoFinalUsd: $model->monto_final_usd !== null ? (float)$model->monto_final_usd : null,
            totalVentasEfectivo: (float)$model->total_ventas_efectivo,
            totalVentasQr: (float)$model->total_ventas_qr,
            totalVentasTarjeta: (float)$model->total_ventas_tarjeta,
            totalFacturado: (float)($model->total_facturado ?? 0.0),
            totalRecibos: (float)($model->total_recibos ?? 0.0),
            totalGastos: (float)$model->total_gastos,
            diferencia: (float)$model->diferencia,
            estado: (string)$model->estado,
            observaciones: $model->observaciones,
            cajeroNombre: $model->cajero ? $model->cajero->name : null,
        );
    }

    private function toMovimientoDomain(MovimientoCajaModel $model): MovimientoCaja
    {
        return new MovimientoCaja(
            id: (int)$model->id,
            tenantId: (string)$model->tenant_id,
            branchId: (string)$model->branch_id,
            turnoId: (int)$model->turno_id,
            usuarioId: (string)$model->usuario_id,
            tipo: (string)$model->tipo,
            monto: (float)$model->monto,
            motivo: (string)$model->motivo,
            comprobanteNro: $model->comprobante_nro,
            observaciones: $model->observaciones,
            createdAt: $model->created_at ? $model->created_at->toDateTimeString() : null,
            usuarioNombre: $model->usuario ? $model->usuario->name : null,
        );
    }
}