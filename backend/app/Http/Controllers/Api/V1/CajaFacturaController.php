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
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoArqueoCiegoModel;
use App\Infrastructure\Persistence\Eloquent\Models\GastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteMovimientoModel;
use App\Infrastructure\Persistence\Eloquent\Models\AnticipoModel;

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
                'total_facturado' => $turnoActualizado->totalFacturado(),
                'total_recibos' => $turnoActualizado->totalRecibos(),
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
                'total_facturado' => $turno->totalFacturado(),
                'total_recibos' => $turno->totalRecibos(),
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
                'message' => 'Debe abrir un turno de caja antes de realizar cobros y facturacion',
            ], 422);
        }

        $isSinMesa = str_starts_with((string)$mesaId, 'sin_mesa_') || $request->has('visita_id');
        if ($isSinMesa) {
            $visitaId = $request->input('visita_id') ?? (int)str_replace('sin_mesa_', '', (string)$mesaId);
            $visita = VisitaModel::with('detalles.producto')->findOrFail($visitaId);
            $mesa = null;
        } else {
            // Find table and active visit
            $mesa = MesaModel::with('visitaActiva.detalles.producto')->findOrFail((int)$mesaId);
            $visita = $mesa->visitaActiva;
        }

        if (!$visita || $visita->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no tiene consumos registrados para cobrar',
            ], 422);
        }

        $validated = $request->validate([
            'tipo_comprobante' => ['nullable', 'string', 'in:FACTURA,RECIBO'],
            'tipo_documento' => ['nullable', 'string', 'in:NIT,CI,CEX,PASAPORTE,OTRO'],
            'numero_documento' => ['nullable', 'string', 'max:30'],
            'razon_social' => ['nullable', 'string', 'max:150'],
            'correo' => ['nullable', 'email', 'max:150'],
            'metodo_pago' => ['required', 'string', 'in:EFECTIVO,QR,TARJETA,MIXTO'],
            'monto_recibido' => ['nullable', 'numeric', 'min:0'],
            'monto_mixto_efectivo' => ['nullable', 'numeric', 'min:0'],
            'monto_mixto_digital' => ['nullable', 'numeric', 'min:0'],
            'segundo_metodo_pago' => ['nullable', 'string', 'in:TARJETA,QR'],
            'datos_adicionales' => ['nullable', 'array'],
        ]);

        $tipoComprobante = $validated['tipo_comprobante'] ?? 'FACTURA';
        $tipoDoc = $validated['tipo_documento'] ?? 'CI';
        $nroDoc = !empty($validated['numero_documento']) ? (string)$validated['numero_documento'] : '0';
        $razonSocial = !empty($validated['razon_social']) ? (string)$validated['razon_social'] : 'SIN NOMBRE';

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
        $fechaEmision = now()->toDateTimeString();
        $cajeroId = $this->resolveUserId($request);

        if ($tipoComprobante === 'FACTURA') {
            $siguienteNro = $this->facturaRepository->getSiguienteNroFactura($tenantId, $branchId);
            $cuf = $this->siatService->generarCuf(
                nroFactura: $siguienteNro,
                fechaEmision: $fechaEmision
            );
            $cufd = $this->siatService->getCufd();
            $codigoSiat = 'VALIDADA_EN_LINEA_SIAT';
            $nroComprobante = 'FAC-' . str_pad((string)$siguienteNro, 6, '0', STR_PAD_LEFT);
        } else {
            // RECIBO / NOTA DE VENTA (Sin Factura Fiscal)
            $siguienteNro = $this->facturaRepository->getSiguienteNroRecibo($tenantId, $branchId);
            $cuf = 'RECIBO-INTERNO-' . $siguienteNro;
            $cufd = null;
            $codigoSiat = 'NO_APLICA_SIN_FACTURA';
            $nroComprobante = 'REC-' . str_pad((string)$siguienteNro, 6, '0', STR_PAD_LEFT);
        }

        // Calculate Breakdown by Payment Method & RestoTech Card Formatting
        $segundoMetodo = $validated['segundo_metodo_pago'] ?? 'TARJETA';
        $montoEf = 0.0;
        $montoTar = 0.0;
        $montoQ = 0.0;
        $nroTarjetaFormateado = null;

        $datosAdicionales = $validated['datos_adicionales'] ?? [];
        $tIni = !empty($datosAdicionales['tarjeta_ini']) ? trim((string)$datosAdicionales['tarjeta_ini']) : '';
        $tFin = !empty($datosAdicionales['tarjeta_fin']) ? trim((string)$datosAdicionales['tarjeta_fin']) : '';

        if ($metodoPago === 'TARJETA' || ($metodoPago === 'MIXTO' && $segundoMetodo === 'TARJETA')) {
            if (strlen($tIni) === 4 && strlen($tFin) === 4) {
                if ($tIni === '0000') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Es obligatorio especificar los primeros 4 digitos de la tarjeta, no puede poner todo 0',
                    ], 422);
                }
                $nroTarjetaFormateado = $tIni . '00000000' . $tFin;
            }
        }

        if ($metodoPago === 'EFECTIVO') {
            $montoEf = $montoRecibido;
        } elseif ($metodoPago === 'TARJETA') {
            $montoTar = $totalConsumo;
        } elseif ($metodoPago === 'QR') {
            $montoQ = $totalConsumo;
        } elseif ($metodoPago === 'MIXTO') {
            $montoEf = (float)($validated['monto_mixto_efectivo'] ?? ($totalConsumo / 2));
            $digital = (float)($validated['monto_mixto_digital'] ?? ($totalConsumo - $montoEf));

            // RestoTech exact validation: sum must match total to pay
            if (abs(($montoEf + $digital) - $totalConsumo) > 0.05) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hay diferencia entre el monto para pagar y los montos seleccionados',
                ], 422);
            }

            if ($segundoMetodo === 'QR') {
                $montoQ = $digital;
            } else {
                $montoTar = $digital;
            }
        }

        // Create Factura or Recibo Record
        $factura = $this->facturaRepository->emitirFactura([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'visita_id' => $visita->id,
            'turno_id' => $turno->id(),
            'cajero_id' => $cajeroId,
            'tipo_comprobante' => $tipoComprobante,
            'nro_comprobante' => $nroComprobante,
            'nro_factura' => $siguienteNro,
            'cuf' => $cuf,
            'cufd' => $cufd,
            'tipo_documento' => $tipoDoc,
            'numero_documento' => $nroDoc,
            'razon_social' => $razonSocial,
            'correo' => $validated['correo'] ?? null,
            'metodo_pago' => $metodoPago,
            'segundo_metodo_pago' => $metodoPago === 'MIXTO' ? $segundoMetodo : null,
            'nro_tarjeta' => $nroTarjetaFormateado,
            'monto_total' => $totalConsumo,
            'monto_efectivo' => $montoEf,
            'monto_tarjeta' => $montoTar,
            'monto_qr' => $montoQ,
            'monto_cambio' => $montoCambio,
            'codigo_siat' => $codigoSiat,
            'fecha_emision' => $fechaEmision,
        ]);

        // Close Visit (Mark COBRADA)
        $visita->update([
            'estado' => 'COBRADA',
            'monto_cobrado' => $totalConsumo,
            'fecha_cierre' => now(),
        ]);

        // Release Table (LIBRE) if physical table exists
        if ($mesa) {
            $mesa->update(['estado' => 'LIBRE']);
        }

        // Recalculate Shift Totals
        $turnoActualizado = $this->cajaRepository->recalcularTotalesTurno($turno->id());

        $qrUrl = ($tipoComprobante === 'FACTURA')
            ? $this->siatService->generarQrUrl(
                nitEmisor: '1028456023',
                cuf: $cuf,
                nroFactura: $siguienteNro,
                total: $totalConsumo,
                fecha: $fechaEmision
            )
            : null;

        $mesaCodigo = $mesa ? ($mesa->codigo ?? $mesa->numero ?? $mesa->nombre) : ($visita->tipo_despacho . ' (' . ($visita->cliente_nombre ?? 'Cliente') . ')');

        $msg = ($tipoComprobante === 'FACTURA')
            ? "Cuenta {$mesaCodigo} cobrada y Factura N° {$siguienteNro} emitida con exito"
            : "Cuenta {$mesaCodigo} cobrada con Recibo N° {$nroComprobante} (Sin Factura) con exito";

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'factura' => [
                    'id' => $factura->id(),
                    'tipo_comprobante' => $factura->tipoComprobante(),
                    'nro_comprobante' => $factura->nroComprobante(),
                    'nro_factura' => $factura->nroFactura(),
                    'cuf' => $factura->cuf(),
                    'cufd' => $factura->cufd(),
                    'tipo_documento' => $factura->tipoDocumento(),
                    'numero_documento' => $factura->numeroDocumento(),
                    'razon_social' => $factura->razonSocial(),
                    'metodo_pago' => $factura->metodoPago(),
                    'segundo_metodo_pago' => $factura->segundoMetodoPago(),
                    'nro_tarjeta' => $factura->nroTarjeta(),
                    'monto_efectivo' => $factura->montoEfectivo(),
                    'monto_tarjeta' => $factura->montoTarjeta(),
                    'monto_qr' => $factura->montoQr(),
                    'monto_total' => $factura->montoTotal(),
                    'monto_recibido' => $montoRecibido,
                    'cambio' => $montoCambio,
                    'fecha_emision' => $factura->fechaEmision(),
                    'cajero' => $factura->cajeroNombre(),
                    'mesa_numero' => $mesa ? ($mesa->numero ?? $mesa->codigo) : $visita->tipo_despacho,
                    'qr_url' => $qrUrl,
                    'detalles' => $factura->detalles(),
                ],
                'turno' => [
                    'total_ventas_efectivo' => $turnoActualizado->totalVentasEfectivo(),
                    'total_ventas_qr' => $turnoActualizado->totalVentasQr(),
                    'total_ventas_tarjeta' => $turnoActualizado->totalVentasTarjeta(),
                    'total_facturado' => $turnoActualizado->totalFacturado(),
                    'total_recibos' => $turnoActualizado->totalRecibos(),
                    'total_ventas' => $turnoActualizado->totalVentas(),
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
    /**
     * Arqueo Ciego de Caja de RestoTech (frmControlCajaTurnoCiego)
     * Desglose de billetes y monedas sin revelar el saldo esperado
     */
    public function realizarArqueoCiego(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'turno_id' => 'required|integer',
            'b200' => 'nullable|integer|min:0',
            'b100' => 'nullable|integer|min:0',
            'b50' => 'nullable|integer|min:0',
            'b20' => 'nullable|integer|min:0',
            'b10' => 'nullable|integer|min:0',
            'm5' => 'nullable|integer|min:0',
            'm2' => 'nullable|integer|min:0',
            'm1' => 'nullable|integer|min:0',
            'm050' => 'nullable|integer|min:0',
            'm020' => 'nullable|integer|min:0',
            'm010' => 'nullable|integer|min:0',
            'monto_manual' => 'nullable|numeric|min:0',
            'cerrar_turno' => 'nullable|boolean',
            'notas' => 'nullable|string|max:500',
        ]);

        $turno = TurnoModel::findOrFail((int) $validated['turno_id']);

        if ($turno->estado !== 'ABIERTO') {
            return response()->json([
                'success' => false,
                'message' => 'El turno especificado ya se encuentra cerrado',
            ], 422);
        }

        // Recalcular turno
        $this->cajaRepository->recalcularTotalesTurno($turno->id);
        $turno->refresh();

        // Conteo billetes
        $b200 = (int) ($validated['b200'] ?? 0);
        $b100 = (int) ($validated['b100'] ?? 0);
        $b50 = (int) ($validated['b50'] ?? 0);
        $b20 = (int) ($validated['b20'] ?? 0);
        $b10 = (int) ($validated['b10'] ?? 0);
        $totalBilletes = ($b200 * 200) + ($b100 * 100) + ($b50 * 50) + ($b20 * 20) + ($b10 * 10);

        // Conteo monedas
        $m5 = (int) ($validated['m5'] ?? 0);
        $m2 = (int) ($validated['m2'] ?? 0);
        $m1 = (int) ($validated['m1'] ?? 0);
        $m050 = (int) ($validated['m050'] ?? 0);
        $m020 = (int) ($validated['m020'] ?? 0);
        $m010 = (int) ($validated['m010'] ?? 0);
        $totalMonedas = ($m5 * 5) + ($m2 * 2) + ($m1 * 1) + ($m050 * 0.5) + ($m020 * 0.2) + ($m010 * 0.1);

        if (isset($validated['monto_manual']) && $validated['monto_manual'] !== null) {
            $totalDeclarado = (float) $validated['monto_manual'];
        } else {
            $totalDeclarado = (float) round($totalBilletes + $totalMonedas, 2);
        }

        // Calcular efectivo esperado del sistema
        $ingresosExtraEfectivo = 0.0;
        if (class_exists(ClienteMovimientoModel::class)) {
            $ingresosExtraEfectivo += (float) ClienteMovimientoModel::where('turno_id', $turno->id)
                ->where('tipo', 'ABONO')
                ->where('metodo_pago', 'EFECTIVO')
                ->sum('monto');
        }
        if (class_exists(AnticipoModel::class)) {
            $ingresosExtraEfectivo += (float) AnticipoModel::where('turno_id', $turno->id)
                ->where('metodo_pago', 'EFECTIVO')
                ->sum('monto');
        }

        $totalEsperado = (float) $turno->monto_inicial_bs 
            + (float) $turno->total_ventas_efectivo 
            + $ingresosExtraEfectivo 
            - (float) $turno->total_gastos;

        $totalEsperado = round($totalEsperado, 2);
        $diferencia = round($totalDeclarado - $totalEsperado, 2);

        if (abs($diferencia) < 0.01) {
            $resultado = 'CUADRADO';
        } elseif ($diferencia > 0) {
            $resultado = 'SOBRANTE';
        } else {
            $resultado = 'FALTANTE';
        }

        $usuario = auth('api')->user() ?? UserModel::first();

        $arqueo = TurnoArqueoCiegoModel::create([
            'turno_id' => $turno->id,
            'usuario_id' => $usuario->id,
            'b200' => $b200,
            'b100' => $b100,
            'b50' => $b50,
            'b20' => $b20,
            'b10' => $b10,
            'm5' => $m5,
            'm2' => $m2,
            'm1' => $m1,
            'm050' => $m050,
            'm020' => $m020,
            'm010' => $m010,
            'total_billetes' => $totalBilletes,
            'total_monedas' => $totalMonedas,
            'total_declarado' => $totalDeclarado,
            'total_esperado' => $totalEsperado,
            'diferencia' => $diferencia,
            'resultado' => $resultado,
            'notas' => $validated['notas'] ?? null,
        ]);

        $cerrado = false;
        if (!empty($validated['cerrar_turno'])) {
            $turno->monto_final_bs = $totalDeclarado;
            $turno->diferencia = $diferencia;
            $turno->estado = 'CERRADO';
            $turno->fecha_fin = now();
            $turno->observaciones = ($turno->observaciones ? $turno->observaciones . ' | ' : '') . 
                "Arqueo Ciego: Declarado Bs. {$totalDeclarado}, Esperado Bs. {$totalEsperado} ({$resultado}: Bs. {$diferencia})";
            $turno->save();
            $cerrado = true;
        }

        return response()->json([
            'success' => true,
            'message' => $cerrado ? 'Arqueo ciego registrado y turno cerrado exitosamente' : 'Arqueo ciego calculado exitosamente',
            'data' => [
                'arqueo_id' => $arqueo->id,
                'turno_id' => $turno->id,
                'total_billetes' => $totalBilletes,
                'total_monedas' => $totalMonedas,
                'total_declarado' => $totalDeclarado,
                'total_esperado' => $totalEsperado,
                'diferencia' => $diferencia,
                'resultado' => $resultado,
                'turno_cerrado' => $cerrado,
                'fecha' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Reporte Z de Cierre de Caja para impresión de ticket térmico
     */
    public function getReporteCierreZ(Request $request, int $turnoId): JsonResponse
    {
        $turno = TurnoModel::with(['cajero', 'tenant', 'branch'])->findOrFail($turnoId);

        $arqueo = TurnoArqueoCiegoModel::where('turno_id', $turno->id)
            ->orderBy('created_at', 'desc')
            ->first();

        $gastos = GastoModel::with('tipoGasto')
            ->where('turno_id', $turno->id)
            ->where('estado', 'ACTIVO')
            ->get();

        $totalVentas = (float) $turno->total_ventas_efectivo + (float) $turno->total_ventas_qr + (float) $turno->total_ventas_tarjeta;

        return response()->json([
            'success' => true,
            'data' => [
                'empresa' => [
                    'nombre' => $turno->tenant->nombre ?? 'RiberResto POS',
                    'sucursal' => $turno->branch->nombre ?? 'Sucursal Central',
                    'ciudad' => 'Riberalta - Beni, Bolivia',
                ],
                'turno' => [
                    'id' => $turno->id,
                    'cajero' => $turno->cajero->name ?? 'Cajero Principal',
                    'fecha_inicio' => $turno->fecha_inicio,
                    'fecha_fin' => $turno->fecha_fin,
                    'estado' => $turno->estado,
                ],
                'fondos' => [
                    'monto_inicial_bs' => (float) $turno->monto_inicial_bs,
                    'total_gastos_bs' => (float) $turno->total_gastos,
                    'monto_final_declarado' => (float) ($turno->monto_final_bs ?? 0.0),
                    'diferencia' => (float) $turno->diferencia,
                ],
                'ventas' => [
                    'efectivo' => (float) $turno->total_ventas_efectivo,
                    'qr' => (float) $turno->total_ventas_qr,
                    'tarjeta' => (float) $turno->total_ventas_tarjeta,
                    'total_ventas' => round($totalVentas, 2),
                ],
                'gastos_detalle' => $gastos->map(fn($g) => [
                    'id' => $g->id,
                    'categoria' => $g->tipoGasto->nombre ?? 'Gasto General',
                    'beneficiario' => $g->beneficiario,
                    'monto' => (float) $g->monto,
                    'forma_pago' => $g->forma_pago,
                ]),
                'arqueo_ciego' => $arqueo ? [
                    'total_billetes' => (float) $arqueo->total_billetes,
                    'total_monedas' => (float) $arqueo->total_monedas,
                    'total_declarado' => (float) $arqueo->total_declarado,
                    'total_esperado' => (float) $arqueo->total_esperado,
                    'diferencia' => (float) $arqueo->diferencia,
                    'resultado' => $arqueo->resultado,
                    'desglose' => [
                        'b200' => $arqueo->b200, 'b100' => $arqueo->b100, 'b50' => $arqueo->b50, 'b20' => $arqueo->b20, 'b10' => $arqueo->b10,
                        'm5' => $arqueo->m5, 'm2' => $arqueo->m2, 'm1' => $arqueo->m1, 'm050' => $arqueo->m050,
                    ],
                ] : null,
            ],
        ]);
    }

    public function cobrarYFacturarVisita(Request $request, int $visitaId): JsonResponse
    {
        return $this->cobrarYFacturar($request, "sin_mesa_{$visitaId}");
    }
}
