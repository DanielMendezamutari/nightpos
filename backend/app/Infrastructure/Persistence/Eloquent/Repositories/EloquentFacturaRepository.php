<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Factura\Entities\Factura;
use App\Domain\Factura\Repositories\FacturaRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;

class EloquentFacturaRepository implements FacturaRepositoryInterface
{
    public function emitirFactura(array $datos): Factura
    {
        $model = FacturaModel::create([
            'tenant_id' => $datos['tenant_id'],
            'branch_id' => $datos['branch_id'],
            'visita_id' => $datos['visita_id'] ?? null,
            'turno_id' => $datos['turno_id'],
            'cajero_id' => $datos['cajero_id'],
            'nro_factura' => $datos['nro_factura'],
            'cuf' => $datos['cuf'],
            'cufd' => $datos['cufd'] ?? null,
            'tipo_documento' => $datos['tipo_documento'] ?? 'NIT',
            'numero_documento' => $datos['numero_documento'],
            'razon_social' => $datos['razon_social'],
            'correo' => $datos['correo'] ?? null,
            'metodo_pago' => $datos['metodo_pago'] ?? 'EFECTIVO',
            'monto_total' => $datos['monto_total'],
            'monto_efectivo' => $datos['monto_efectivo'] ?? 0.0,
            'monto_cambio' => $datos['monto_cambio'] ?? 0.0,
            'estado' => 'VALIDA',
            'codigo_siat' => $datos['codigo_siat'] ?? 'VALIDADA_EN_LINEA',
            'fecha_emision' => $datos['fecha_emision'] ?? now(),
        ]);

        return $this->toDomain($model->load(['cajero', 'visita.mesa', 'visita.detalles.producto']));
    }

    public function getFacturas(string $tenantId, string $branchId, ?int $turnoId = null): array
    {
        $query = FacturaModel::with(['cajero', 'visita.mesa', 'visita.detalles.producto'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        if ($turnoId !== null) {
            $query->where('turno_id', $turnoId);
        }

        $models = $query->latest('id')->limit(100)->get();

        return $models->map(fn($m) => $this->toDomain($m))->all();
    }

    public function findFacturaById(int $id): ?Factura
    {
        $model = FacturaModel::with(['cajero', 'visita.mesa', 'visita.detalles.producto'])->find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function anularFactura(int $id, string $motivo): Factura
    {
        $model = FacturaModel::with(['cajero', 'visita.mesa', 'visita.detalles.producto'])->findOrFail($id);
        $model->update([
            'estado' => 'ANULADA',
            'codigo_siat' => 'ANULADA_EN_LINEA: ' . $motivo,
        ]);

        return $this->toDomain($model->fresh(['cajero', 'visita.mesa', 'visita.detalles.producto']));
    }

    public function getSiguienteNroFactura(string $tenantId, string $branchId): int
    {
        $ultimoNro = FacturaModel::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->max('nro_factura');

        return ($ultimoNro ?? 0) + 1;
    }

    private function toDomain(FacturaModel $model): Factura
    {
        $detalles = [];
        if ($model->visita && $model->visita->detalles) {
            foreach ($model->visita->detalles as $det) {
                $detalles[] = [
                    'id' => $det->id,
                    'producto_id' => $det->producto_id,
                    'producto_nombre' => $det->producto ? $det->producto->nombre : 'Item',
                    'cantidad' => $det->cantidad,
                    'precio_unitario' => (float)$det->precio_unitario,
                    'subtotal' => (float)$det->subtotal,
                ];
            }
        }

        return new Factura(
            id: (int)$model->id,
            tenantId: (string)$model->tenant_id,
            branchId: (string)$model->branch_id,
            visitaId: $model->visita_id ? (int)$model->visita_id : null,
            turnoId: (int)$model->turno_id,
            cajeroId: (string)$model->cajero_id,
            nroFactura: (int)$model->nro_factura,
            cuf: (string)$model->cuf,
            cufd: $model->cufd,
            tipoDocumento: (string)$model->tipo_documento,
            numeroDocumento: (string)$model->numero_documento,
            razonSocial: (string)$model->razon_social,
            correo: $model->correo,
            metodoPago: (string)$model->metodo_pago,
            montoTotal: (float)$model->monto_total,
            montoEfectivo: (float)$model->monto_efectivo,
            montoCambio: (float)$model->monto_cambio,
            estado: (string)$model->estado,
            codigoSiat: $model->codigo_siat,
            fechaEmision: $model->fecha_emision ? $model->fecha_emision->toDateTimeString() : '',
            cajeroNombre: $model->cajero ? $model->cajero->name : null,
            mesaNumero: ($model->visita && $model->visita->mesa) ? (string)$model->visita->mesa->numero : null,
            detalles: $detalles,
        );
    }
}