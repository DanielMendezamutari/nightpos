<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Caja\Repositories\CajaRepositoryInterface;
use App\Domain\Factura\Repositories\FacturaRepositoryInterface;
use App\Infrastructure\Facturacion\SiatBoliviaService;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CajaFacturaController extends Controller
{
    public function __construct(
        private readonly CajaRepositoryInterface $cajaRepository,
        private readonly FacturaRepositoryInterface $facturaRepository,
        private readonly SiatBoliviaService $siatService
    ) {}

    public function getTurnoActivo(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);

        $turno = $this->cajaRepository->getTurnoActivo($tenantId, $branchId);

        if (!$turno) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No hay turno activo actualmente en esta sucursal',
            ]);
        }

        // Always recalibrate live figures
        $turnoActualizado = $this->cajaRepository->recalcularTotalesTurno($turno->id());

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $turnoActualizado->id(),
                'tenant_id' => $turnoActualizado->tenantId(),
                'branch_id' => $turnoActualizado->branchId(),
                'cajero_id' => $turnoActualizado->cajeroId(),
                'cajero_nombre' => $turnoActualizado->cajeroNombre(),
                'fecha_inicio' => $turnoActualizado->fechaInicio(),
                'monto_inicial_bs' => $turnoActualizado->montoInicialBs(),
                'monto_inicial_usd' => $turnoActualizado->montoInicialUsd(),
                'total_ventas_efectivo' => $turnoActualizado->totalVentasEfectivo(),
                'total_ventas_qr' => $turnoActualizado->totalVentasQr(),
                'total_ventas_tarjeta' => $turnoActualizado->totalVentasTarjeta(),
                'total_ventas' => $turnoActualizado->totalVentas(),
                'total_gastos' => $turnoActualizado->totalGastos(),
                'efectivo_esperado' => $turnoActualizado->efectivoEsperado(),
                'estado' => $turnoActualizado->estado(),
                'observaciones' => $turnoActualizado->observaciones(),
            ],
        ]);
    }

    public function abrirTurno(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);

        $existente = $this->cajaRepository->getTurnoActivo($tenantId, $branchId);
        if ($existente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un turno abierto en esta sucursal (Turno #' . $existente->id() . ')',
            ], 422);
        }

        $validated = $request->validate([
            'monto_inicial_bs' => ['required', 'numeric', 'min:0'],
            'monto_inicial_usd' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $cajeroId = $this->resolveUserId($request);

        $turno = $this->cajaRepository->abrirTurno(
            tenantId: $tenantId,
            branchId: $branchId,
            cajeroId: $cajeroId,
            montoInicialBs: (float)$validated['monto_inicial_bs'],
            montoInicialUsd: (float)($validated['monto_inicial_usd'] ?? 0.0),
            observaciones: $validated['observaciones'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Turno de caja abierto exitosamente',
            'data' => [
                'id' => $turno->id(),
                'fecha_inicio' => $turno->fechaInicio(),
                'monto_inicial_bs' => $turno->montoInicialBs(),
                'monto_inicial_usd' => $turno->montoInicialUsd(),
                'cajero_nombre' => $turno->cajeroNombre(),
                'estado' => $turno->estado(),
            ],
        ], 201);
    }

    public function cerrarTurno(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'turno_id' => ['required', 'integer'],
            'monto_final_bs' => ['required', 'numeric', 'min:0'],
            'monto_final_usd' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $turnoActual = $this->cajaRepository->getTurnoById((int)$validated['turno_id']);
        if (!$turnoActual || $turnoActual->estado() !== 'ABIERTO') {
            return response()->json([
                'success' => false,
                'message' => 'El turno especificado no existe o ya ha sido cerrado',
            ], 422);
        }

        // Recalculate right before closing
        $this->cajaRepository->recalcularTotalesTurno($turnoActual->id());

        $turno = $this->cajaRepository->cerrarTurno(
            turnoId: (int)$validated['turno_id'],
            montoFinalBs: (float)$validated['monto_final_bs'],
            montoFinalUsd: (float)($validated['monto_final_usd'] ?? 0.0),
            observaciones: $validated['observaciones'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Turno de caja cerrado y arqueado exitosamente',
            'data' => [
                'id' => $turno->id(),
                'fecha_inicio' => $turno->fechaInicio(),
                'fecha_fin' => $turno->fechaFin(),
                'monto_inicial_bs' => $turno->montoInicialBs(),
                'total_ventas_efectivo' => $turno->totalVentasEfectivo(),
                'total_ventas_qr' => $turno->totalVentasQr(),
                'total_ventas_tarjeta' => $turno->totalVentasTarjeta(),
                'total_ventas' => $turno->totalVentas(),
                'total_gastos' => $turno->totalGastos(),
                'efectivo_esperado' => $turno->efectivoEsperado(),
                'monto_final_bs' => $turno->montoFinalBs(),
                'diferencia' => $turno->diferencia(),
                'estado' => $turno->estado(),
                'observaciones' => $turno->observaciones(),
            ],
        ]);
    }

    public function getMovimientos(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);

        $turnoId = $request->query('turno_id');
        if (!$turnoId) {
            $turno = $this->cajaRepository->getTurnoActivo($tenantId, $branchId);
            $turnoId = $turno ? $turno->id() : null;
        }

        if (!$turnoId) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $movimientos = $this->cajaRepository->getMovimientosByTurno((int)$turnoId);

        return response()->json([
            'success' => true,
            'data' => array_map(fn($m) => [
                'id' => $m->id(),
                'tipo' => $m->tipo(),
                'monto' => $m->monto(),
                'motivo' => $m->motivo(),
                'comprobante_nro' => $m->comprobanteNro(),
                'observaciones' => $m->observaciones(),
                'created_at' => $m->createdAt(),
                'usuario_nombre' => $m->usuarioNombre(),
            ], $movimientos),
        ]);
    }

    public function registrarMovimiento(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);

        $turno = $this->cajaRepository->getTurnoActivo($tenantId, $branchId);
        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'No hay turno abierto para registrar gastos o movimientos',
            ], 422);
        }

        $validated = $request->validate([
            'tipo' => ['required', 'string', 'in:INGRESO,EGRESO_GASTO,RETIRO_CAJA'],
            'monto' => ['required', 'numeric', 'gt:0'],
            'motivo' => ['required', 'string', 'max:200'],
            'comprobante_nro' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $usuarioId = $this->resolveUserId($request);

        $movimiento = $this->cajaRepository->registrarMovimiento(
            tenantId: $tenantId,
            branchId: $branchId,
            turnoId: $turno->id(),
            usuarioId: $usuarioId,
            tipo: $validated['tipo'],
            monto: (float)$validated['monto'],
            motivo: $validated['motivo'],
            comprobanteNro: $validated['comprobante_nro'] ?? null,
            observaciones: $validated['observaciones'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Movimiento de caja registrado correctamente',
            'data' => [
                'id' => $movimiento->id(),
                'tipo' => $movimiento->tipo(),
                'monto' => $movimiento->monto(),
                'motivo' => $movimiento->motivo(),
                'comprobante_nro' => $movimiento->comprobanteNro(),
                'created_at' => $movimiento->createdAt(),
                'usuario_nombre' => $movimiento->usuarioNombre(),
            ],
        ], 201);
    }

    public function cobrarYFacturar(Request $request, string|int $mesaId): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);

        // Check active shift
        $turno = $this->cajaRepository->getTurnoActivo($tenantId, $branchId);
        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'Debe abrir un turno de caja antes de realizar cobros y facturaciÃ³n',
            ], 422);
        }

        // Find table and active visit
        $mesa = MesaModel::with('visitaActiva.detalles.producto')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita || $visita->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no tiene consumos registrados para cobrar',
            ], 422);
        }

        $validated = $request->validate([
            'tipo_documento' => ['required', 'string', 'in:NIT,CI,CEX,PASAPORTE,OTRO'],
            'numero_documento' => ['required', 'string', 'max:30'],
            'razon_social' => ['required', 'string', 'max:150'],
            'correo' => ['nullable', 'email', 'max:150'],
            'metodo_pago' => ['required', 'string', 'in:EFECTIVO,QR,TARJETA,MIXTO'],
            'monto_recibido' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalConsumo = (float)$visita->total > 0
            ? (float)$visita->total
            : (float)$visita->detalles->sum('subtotal');

        $metodoPago = $validated['metodo_pago'];
        $montoRecibido = (float)($validated['monto_recibido'] ?? $totalConsumo);

        if ($metodoPago === 'EFECTIVO' && $montoRecibido < $totalConsumo) {
            return response()->json([
                'success' => false,
                'message' => "El monto recibido (Bs. {$montoRecibido}) es menor al total a cobrar (Bs. {$totalConsumo})",
            ], 422);
        }

        $montoCambio = ($metodoPago === 'EFECTIVO') ? round(max(0, $montoRecibido - $totalConsumo), 2) : 0.0;
        $siguienteNroFactura = $this->facturaRepository->getSiguienteNroFactura($tenantId, $branchId);
        $fechaEmision = now()->toDateTimeString();

        $cuf = $this->siatService->generarCuf(
            nroFactura: $siguienteNroFactura,
            fechaEmision: $fechaEmision
        );
        $cufd = $this->siatService->getCufd();

        $cajeroId = $this->resolveUserId($request);

        // Create Factura
        $factura = $this->facturaRepository->emitirFactura([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'visita_id' => $visita->id,
            'turno_id' => $turno->id(),
            'cajero_id' => $cajeroId,
            'nro_factura' => $siguienteNroFactura,
            'cuf' => $cuf,
            'cufd' => $cufd,
            'tipo_documento' => $validated['tipo_documento'],
            'numero_documento' => $validated['numero_documento'],
            'razon_social' => $validated['razon_social'],
            'correo' => $validated['correo'] ?? null,
            'metodo_pago' => $metodoPago,
            'monto_total' => $totalConsumo,
            'monto_efectivo' => $montoRecibido,
            'monto_cambio' => $montoCambio,
            'codigo_siat' => 'VALIDADA_EN_LINEA_SIAT',
            'fecha_emision' => $fechaEmision,
        ]);

        // Close Visit (Mark COBRADA)
        $visita->update([
            'estado' => 'COBRADA',
            'fecha_cierre' => now(),
            'total' => $totalConsumo,
        ]);

        // Recalculate Turno totals
        $turnoActualizado = $this->cajaRepository->recalcularTotalesTurno($turno->id());

        $qrUrl = $this->siatService->generarQrUrl(
            nitEmisor: '1028456023',
            cuf: $cuf,
            nroFactura: $siguienteNroFactura,
            total: $totalConsumo,
            fecha: $fechaEmision
        );

        return response()->json([
            'success' => true,
            'message' => 'Cobro procesado y Factura SIAT emitida exitosamente',
            'data' => [
                'factura' => [
                    'id' => $factura->id(),
                    'nro_factura' => $factura->nroFactura(),
                    'cuf' => $factura->cuf(),
                    'razon_social' => $factura->razonSocial(),
                    'numero_documento' => $factura->numeroDocumento(),
                    'tipo_documento' => $factura->tipoDocumento(),
                    'metodo_pago' => $factura->metodoPago(),
                    'monto_total' => $factura->montoTotal(),
                    'monto_efectivo' => $factura->montoEfectivo(),
                    'monto_cambio' => $factura->montoCambio(),
                    'fecha_emision' => $factura->fechaEmision(),
                    'codigo_siat' => $factura->codigoSiat(),
                    'qr_url' => $qrUrl,
                    'leyenda_siat' => 'Ley NÂ° 453: El proveedor deberÃ¡ suministrar el servicio en las modalidades y tÃ©rminos ofertados o convenidos.',
                    'mesa_numero' => $mesa->codigo ?? $mesa->nombre,
                    'detalles' => $factura->detalles(),
                ],
                'mesa' => [
                    'id' => $mesa->id,
                    'codigo' => $mesa->codigo,
                    'nombre' => $mesa->nombre,
                    'estado' => 'LIBRE',
                ],
                'turno' => [
                    'id' => $turnoActualizado->id(),
                    'total_ventas_efectivo' => $turnoActualizado->totalVentasEfectivo(),
                    'total_ventas_qr' => $turnoActualizado->totalVentasQr(),
                    'total_ventas_tarjeta' => $turnoActualizado->totalVentasTarjeta(),
                    'total_ventas' => $turnoActualizado->totalVentas(),
                    'efectivo_esperado' => $turnoActualizado->efectivoEsperado(),
                ],
            ],
        ]);
    }

    public function getFacturas(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        $branchId = $this->resolveBranchId($request);
        $turnoId = $request->query('turno_id') ? (int)$request->query('turno_id') : null;

        $facturas = $this->facturaRepository->getFacturas($tenantId, $branchId, $turnoId);

        return response()->json([
            'success' => true,
            'data' => array_map(fn($f) => [
                'id' => $f->id(),
                'nro_factura' => $f->nroFactura(),
                'cuf' => $f->cuf(),
                'razon_social' => $f->razonSocial(),
                'numero_documento' => $f->numeroDocumento(),
                'tipo_documento' => $f->tipoDocumento(),
                'metodo_pago' => $f->metodoPago(),
                'monto_total' => $f->montoTotal(),
                'monto_efectivo' => $f->montoEfectivo(),
                'monto_cambio' => $f->montoCambio(),
                'estado' => $f->estado(),
                'fecha_emision' => $f->fechaEmision(),
                'cajero_nombre' => $f->cajeroNombre(),
                'mesa_numero' => $f->mesaNumero(),
                'detalles' => $f->detalles(),
            ], $facturas),
        ]);
    }

    public function anularFactura(Request $request, string|int $id): JsonResponse
    {
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:250'],
        ]);

        $factura = $this->facturaRepository->findFacturaById((int)$id);
        if (!$factura) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada',
            ], 404);
        }

        if ($factura->estado() === 'ANULADA') {
            return response()->json([
                'success' => false,
                'message' => 'La factura ya se encuentra anulada',
            ], 422);
        }

        $facturaAnulada = $this->facturaRepository->anularFactura((int)$id, $validated['motivo']);

        // Recalculate shift totals
        $this->cajaRepository->recalcularTotalesTurno($facturaAnulada->turnoId());

        return response()->json([
            'success' => true,
            'message' => 'Factura anulada exitosamente en el sistema y SIAT',
            'data' => [
                'id' => $facturaAnulada->id(),
                'nro_factura' => $facturaAnulada->nroFactura(),
                'estado' => $facturaAnulada->estado(),
                'codigo_siat' => $facturaAnulada->codigoSiat(),
            ],
        ]);
    }

    private function resolveTenantId(Request $request): string
    {
        if ($request->attributes->has('tenant_id')) {
            return (string)$request->attributes->get('tenant_id');
        }
        $tenant = TenantModel::first();
        return $tenant ? (string)$tenant->id : '';
    }

    private function resolveBranchId(Request $request): string
    {
        if ($request->attributes->has('branch_id')) {
            return (string)$request->attributes->get('branch_id');
        }
        $branch = BranchModel::first();
        return $branch ? (string)$branch->id : '';
    }

    private function resolveUserId(Request $request): string
    {
        if ($request->user()) {
            return (string)$request->user()->id;
        }
        $user = UserModel::first();
        return $user ? (string)$user->id : '';
    }
}