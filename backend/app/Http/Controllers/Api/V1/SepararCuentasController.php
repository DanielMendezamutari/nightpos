<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Facturacion\SiatBoliviaService;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoCajaModel;
use App\Infrastructure\Persistence\Eloquent\Models\PagoParcialVisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\PropinaModel;
use App\Infrastructure\Persistence\Eloquent\Models\SubcuentaVisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SepararCuentasController extends Controller
{
    public function __construct(
        private readonly SiatBoliviaService $siatService = new SiatBoliviaService(),
    ) {}

    /**
     * Asegura que exista al menos la Subcuenta 1 principal y que los detalles estén asignados
     */
    private function asegurarSubcuentaPrincipal(VisitaModel $visita): SubcuentaVisitaModel
    {
        $principal = SubcuentaVisitaModel::where('visita_id', $visita->id)
            ->where('numero_subcuenta', 1)
            ->first();

        if (!$principal) {
            $principal = SubcuentaVisitaModel::create([
                'tenant_id' => $visita->tenant_id,
                'branch_id' => $visita->branch_id,
                'visita_id' => $visita->id,
                'numero_subcuenta' => 1,
                'nombre_comensal' => 'Cuenta Principal',
                'estado' => 'PENDIENTE',
                'total' => (float)$visita->total,
                'monto_pagado' => 0.00,
            ]);
        }

        // Asignar detalles huérfanos a la cuenta principal
        VisitaDetalleModel::where('visita_id', $visita->id)
            ->whereNull('subcuenta_id')
            ->update(['subcuenta_id' => $principal->id]);

        return $principal;
    }

    /**
     * Recalcula totales de las subcuentas de una visita
     */
    private function recalcularTotalesSubcuentas(int $visitaId): void
    {
        $subcuentas = SubcuentaVisitaModel::where('visita_id', $visitaId)->get();
        foreach ($subcuentas as $sub) {
            $total = (float) VisitaDetalleModel::where('subcuenta_id', $sub->id)
                ->where('estado', '!=', 'CANCELADO')
                ->sum('subtotal');
            $sub->total = round($total, 2);
            $sub->save();
        }

        // Actualizar total general de la visita
        $visita = VisitaModel::find($visitaId);
        if ($visita) {
            $totalVisita = (float) VisitaDetalleModel::where('visita_id', $visitaId)
                ->where('estado', '!=', 'CANCELADO')
                ->sum('subtotal');
            $visita->total = round($totalVisita, 2);
            $visita->save();
        }
    }

    /**
     * Obtener todas las subcuentas y estado de separación de una mesa
     */
    public function getSubcuentas(Request $request, string|int $mesaId): JsonResponse
    {
        $mesa = MesaModel::with(['visitaActiva.detalles.producto', 'visitaActiva.mesero'])->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no tiene una visita o comanda activa.',
            ], 422);
        }

        $this->asegurarSubcuentaPrincipal($visita);
        $this->recalcularTotalesSubcuentas($visita->id);

        $subcuentas = SubcuentaVisitaModel::with(['detalles.producto', 'factura'])
            ->where('visita_id', $visita->id)
            ->orderBy('numero_subcuenta', 'asc')
            ->get();

        $pagosParciales = PagoParcialVisitaModel::with('cajero')
            ->where('visita_id', $visita->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalPagosParciales = (float) $pagosParciales->sum('monto');
        $totalSubcuentasCobradas = (float) $subcuentas->where('estado', 'COBRADA')->sum('monto_pagado');
        $totalPagado = $totalPagosParciales + $totalSubcuentasCobradas;
        $totalVisita = (float) $visita->total;
        $saldoPendiente = round(max(0, $totalVisita - $totalPagado), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'mesa_id' => $mesa->id,
                'mesa_numero' => $mesa->numero,
                'visita_id' => $visita->id,
                'mesero' => $visita->mesero ? ['id' => $visita->mesero->id, 'name' => $visita->mesero->name] : null,
                'total_visita' => $totalVisita,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => $saldoPendiente,
                'subcuentas' => $subcuentas,
                'pagos_parciales' => $pagosParciales,
            ],
        ]);
    }

    /**
     * Crear una nueva subcuenta vacía para la mesa (Cuenta 2, Cuenta 3...)
     */
    public function crearSubcuenta(Request $request, string|int $mesaId): JsonResponse
    {
        $mesa = MesaModel::with('visitaActiva')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no tiene una visita activa.',
            ], 422);
        }

        $validated = $request->validate([
            'nombre_comensal' => 'nullable|string|max:100',
        ]);

        $nextNum = SubcuentaVisitaModel::where('visita_id', $visita->id)->max('numero_subcuenta') + 1;
        $nombre = !empty($validated['nombre_comensal'])
            ? $validated['nombre_comensal']
            : "Cuenta {$nextNum}";

        $subcuenta = SubcuentaVisitaModel::create([
            'tenant_id' => $visita->tenant_id,
            'branch_id' => $visita->branch_id,
            'visita_id' => $visita->id,
            'numero_subcuenta' => $nextNum,
            'nombre_comensal' => $nombre,
            'estado' => 'PENDIENTE',
            'total' => 0.00,
            'monto_pagado' => 0.00,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Subcuenta '{$nombre}' creada correctamente",
            'data' => $subcuenta,
        ], 201);
    }

    /**
     * Mover ítem o cantidad entre subcuentas (frmSepararCuentas RestoTech)
     */
    public function moverItemSubcuenta(Request $request, string|int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'detalle_id' => 'required|integer|exists:visita_detalles,id',
            'subcuenta_destino_id' => 'required|integer|exists:subcuentas_visita,id',
            'cantidad' => 'nullable|numeric|min:0.5',
        ]);

        $mesa = MesaModel::with('visitaActiva')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'No hay visita activa'], 422);
        }

        $detalle = VisitaDetalleModel::where('visita_id', $visita->id)->findOrFail($validated['detalle_id']);
        $subDestino = SubcuentaVisitaModel::where('visita_id', $visita->id)->findOrFail($validated['subcuenta_destino_id']);

        if ($subDestino->estado === 'COBRADA') {
            return response()->json([
                'success' => false,
                'message' => 'No se pueden transferir productos a una subcuenta que ya fue cobrada.',
            ], 422);
        }

        $cantidadMover = isset($validated['cantidad']) ? (float)$validated['cantidad'] : (float)$detalle->cantidad;

        if ($cantidadMover > (float)$detalle->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'La cantidad a transferir excede la cantidad disponible del producto.',
            ], 422);
        }

        DB::transaction(function () use ($detalle, $subDestino, $cantidadMover, $visita) {
            if ($cantidadMover == (float)$detalle->cantidad) {
                // Mover el registro completo
                $detalle->subcuenta_id = $subDestino->id;
                $detalle->save();
            } else {
                // Separar fraccionado: descontar del origen y crear nuevo en destino
                $nuevaCantOrigen = (float)$detalle->cantidad - $cantidadMover;
                $detalle->cantidad = $nuevaCantOrigen;
                $detalle->subtotal = round($nuevaCantOrigen * (float)$detalle->precio_unitario, 2);
                $detalle->save();

                VisitaDetalleModel::create([
                    'visita_id' => $visita->id,
                    'subcuenta_id' => $subDestino->id,
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto_nombre,
                    'cantidad' => $cantidadMover,
                    'precio_unitario' => (float)$detalle->precio_unitario,
                    'subtotal' => round($cantidadMover * (float)$detalle->precio_unitario, 2),
                    'observaciones' => $detalle->observaciones,
                    'estacion_cocina' => $detalle->estacion_cocina,
                    'estado' => $detalle->estado,
                ]);
            }

            $this->recalcularTotalesSubcuentas($visita->id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Producto transferido exitosamente entre cuentas',
        ]);
    }

    /**
     * División Equitativa en N partes iguales (Split Nx)
     */
    public function dividirEnPartesIguales(Request $request, string|int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'personas' => 'required|integer|min:2|max:20',
        ]);

        $mesa = MesaModel::with('visitaActiva.detalles')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'No hay visita activa'], 422);
        }

        $n = (int)$validated['personas'];
        $total = (float)$visita->detalles->where('estado', '!=', 'CANCELADO')->sum('subtotal');
        $cuotaBase = round($total / $n, 2);

        DB::transaction(function () use ($visita, $n, $total, $cuotaBase) {
            // Eliminar subcuentas pendientes previas
            SubcuentaVisitaModel::where('visita_id', $visita->id)
                ->where('estado', 'PENDIENTE')
                ->delete();

            $acumulado = 0.0;
            for ($i = 1; $i <= $n; $i++) {
                $montoCuota = ($i === $n) ? round($total - $acumulado, 2) : $cuotaBase;
                $acumulado += $montoCuota;

                SubcuentaVisitaModel::create([
                    'tenant_id' => $visita->tenant_id,
                    'branch_id' => $visita->branch_id,
                    'visita_id' => $visita->id,
                    'numero_subcuenta' => $i,
                    'nombre_comensal' => "Persona {$i} (1/{$n})",
                    'estado' => 'PENDIENTE',
                    'total' => $montoCuota,
                    'monto_pagado' => 0.00,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Cuenta dividida exitosamente en {$n} partes iguales de Bs. {$cuotaBase}",
            'data' => SubcuentaVisitaModel::where('visita_id', $visita->id)->get(),
        ]);
    }

    /**
     * Juntar Cuentas (juntarCuentas de RestoTech): Reunifica todas las subcuentas pendientes en la Cuenta 1
     */
    public function juntarCuentas(Request $request, string|int $mesaId): JsonResponse
    {
        $mesa = MesaModel::with('visitaActiva')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'No hay visita activa'], 422);
        }

        $principal = $this->asegurarSubcuentaPrincipal($visita);

        DB::transaction(function () use ($visita, $principal) {
            // Migrar todos los ítems de subcuentas pendientes hacia la cuenta principal
            $subcuentasPendientesIds = SubcuentaVisitaModel::where('visita_id', $visita->id)
                ->where('id', '!=', $principal->id)
                ->where('estado', 'PENDIENTE')
                ->pluck('id');

            VisitaDetalleModel::whereIn('subcuenta_id', $subcuentasPendientesIds)
                ->update(['subcuenta_id' => $principal->id]);

            // Eliminar las subcuentas pendientes vacías
            SubcuentaVisitaModel::whereIn('id', $subcuentasPendientesIds)->delete();

            $this->recalcularTotalesSubcuentas($visita->id);
        });

        return response()->json([
            'success' => true,
            'message' => 'Cuentas unificadas exitosamente en la Cuenta Principal',
        ]);
    }

    /**
     * Pago Parcial a Cuenta de Mesa (frmPagoParcial RestoTech)
     */
    public function registrarPagoParcial(Request $request, string|int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.50',
            'metodo_pago' => 'required|string|in:EFECTIVO,QR,TARJETA',
            'notas' => 'nullable|string|max:200',
        ]);

        $mesa = MesaModel::with('visitaActiva')->findOrFail((int)$mesaId);
        $visita = $mesa->visitaActiva;

        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'No hay visita activa'], 422);
        }

        $turno = TurnoModel::where('estado', 'ABIERTO')->first();
        if (!$turno) {
            return response()->json(['success' => false, 'message' => 'No hay turno de caja abierto'], 422);
        }

        $usuario = auth('api')->user() ?? UserModel::first();
        $monto = (float)$validated['monto'];
        $metodo = $validated['metodo_pago'];

        $pago = DB::transaction(function () use ($visita, $turno, $usuario, $monto, $metodo, $validated, $mesa) {
            $pagoParcial = PagoParcialVisitaModel::create([
                'tenant_id' => $visita->tenant_id,
                'branch_id' => $visita->branch_id,
                'visita_id' => $visita->id,
                'turno_id' => $turno->id,
                'cajero_id' => $usuario->id,
                'monto' => $monto,
                'metodo_pago' => $metodo,
                'comprobante_nro' => 'PARCIAL-' . strtoupper(substr(uniqid(), -6)),
                'notas' => $validated['notas'] ?? "Abono parcial mesa #{$mesa->numero}",
            ]);

            // Actualizar turno de caja
            if ($metodo === 'EFECTIVO') {
                $turno->total_ventas_efectivo = (float)$turno->total_ventas_efectivo + $monto;
            } elseif ($metodo === 'QR') {
                $turno->total_ventas_qr = (float)$turno->total_ventas_qr + $monto;
            } elseif ($metodo === 'TARJETA') {
                $turno->total_ventas_tarjeta = (float)$turno->total_ventas_tarjeta + $monto;
            }
            $turno->total_recibos = (float)$turno->total_recibos + $monto;
            $turno->save();

            // Verificar si el saldo total de la visita ya quedó totalmente saldado
            $totalAbonado = (float) PagoParcialVisitaModel::where('visita_id', $visita->id)->sum('monto');
            if ($totalAbonado >= (float)$visita->total) {
                $visita->estado = 'COBRADA';
                $visita->fecha_cierre = now();
                $visita->save();

                // Mesa queda libre al cerrar visita
            }

            return $pagoParcial;
        });

        return response()->json([
            'success' => true,
            'message' => "Pago parcial de Bs. {$monto} registrado exitosamente",
            'data' => $pago,
        ], 201);
    }

    /**
     * Cobrar una Subcuenta individual con Factura / Recibo y Propina opcional
     */
    public function cobrarSubcuenta(Request $request, int $subcuentaId): JsonResponse
    {
        $subcuenta = SubcuentaVisitaModel::with(['visita.mesa', 'visita.mesero', 'detalles.producto'])
            ->findOrFail($subcuentaId);

        if ($subcuenta->estado === 'COBRADA') {
            return response()->json([
                'success' => false,
                'message' => 'Esta subcuenta ya se encuentra cobrada.',
            ], 422);
        }

        $turno = TurnoModel::where('estado', 'ABIERTO')->first();
        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un turno de caja abierto para realizar cobros.',
            ], 422);
        }

        $validated = $request->validate([
            'tipo_comprobante' => 'nullable|string|in:FACTURA,RECIBO',
            'tipo_documento' => 'nullable|string|in:NIT,CI,CEX,PASAPORTE,OTRO',
            'numero_documento' => 'nullable|string|max:30',
            'razon_social' => 'nullable|string|max:150',
            'correo' => 'nullable|email|max:150',
            'metodo_pago' => 'required|string|in:EFECTIVO,QR,TARJETA,MIXTO',
            'monto_recibido' => 'nullable|numeric|min:0',
            'monto_mixto_efectivo' => 'nullable|numeric|min:0',
            'monto_mixto_digital' => 'nullable|numeric|min:0',
            'segundo_metodo_pago' => 'nullable|string|in:TARJETA,QR',
            'propina_monto' => 'nullable|numeric|min:0',
            'propina_porcentaje' => 'nullable|numeric|min:0',
        ]);

        $usuario = auth('api')->user() ?? UserModel::first();
        $tipoComprobante = $validated['tipo_comprobante'] ?? 'FACTURA';
        $metodoPago = $validated['metodo_pago'];
        $montoSubcuenta = (float)$subcuenta->total;
        $propinaMonto = (float)($validated['propina_monto'] ?? 0.00);

        return DB::transaction(function () use ($subcuenta, $turno, $usuario, $validated, $tipoComprobante, $metodoPago, $montoSubcuenta, $propinaMonto) {
            $visita = $subcuenta->visita;
            $mesa = $visita->mesa;

            // 1. Crear Factura o Recibo Fiscal
            $siguienteNro = FacturaModel::where('tenant_id', $turno->tenant_id)
                ->where('tipo_comprobante', $tipoComprobante)
                ->max('nro_factura') + 1;

            if ($tipoComprobante === 'FACTURA') {
                $cuf = $this->siatService->generarCuf($siguienteNro, now()->toDateTimeString());
                $cufd = $this->siatService->getCufd();
                $codigoSiat = 'VALIDADA_EN_LINEA_SIAT';
            } else {
                $cuf = 'RECIBO-SUBCUENTA-' . $siguienteNro;
                $cufd = null;
                $codigoSiat = 'NO_APLICA_SIN_FACTURA';
            }

            $factura = FacturaModel::create([
                'tenant_id' => $turno->tenant_id,
                'branch_id' => $turno->branch_id,
                'visita_id' => $visita->id,
                'turno_id' => $turno->id,
                'cajero_id' => $usuario->id,
                'tipo_comprobante' => $tipoComprobante,
                'nro_factura' => $siguienteNro,
                'cuf' => $cuf,
                'cufd' => $cufd,
                'tipo_documento' => $validated['tipo_documento'] ?? 'CI',
                'numero_documento' => $validated['numero_documento'] ?? '0',
                'razon_social' => $validated['razon_social'] ?? 'SIN NOMBRE',
                'correo' => $validated['correo'] ?? null,
                'metodo_pago' => $metodoPago,
                'monto_total' => $montoSubcuenta,
                'monto_efectivo' => ($metodoPago === 'EFECTIVO') ? (float)($validated['monto_recibido'] ?? $montoSubcuenta) : 0.00,
                'monto_cambio' => ($metodoPago === 'EFECTIVO') ? max(0, (float)($validated['monto_recibido'] ?? $montoSubcuenta) - $montoSubcuenta) : 0.00,
                'monto_mixto_efectivo' => $validated['monto_mixto_efectivo'] ?? null,
                'monto_mixto_digital' => $validated['monto_mixto_digital'] ?? null,
                'segundo_metodo_pago' => $validated['segundo_metodo_pago'] ?? null,
                'estado' => 'EMITIDA',
                'codigo_siat' => $codigoSiat,
                'fecha_emision' => now(),
            ]);

            // 2. Si hay propina, registrar en modelo Propina (sin gravar impuestos)
            if ($propinaMonto > 0) {
                PropinaModel::create([
                    'tenant_id' => $turno->tenant_id,
                    'branch_id' => $turno->branch_id,
                    'visita_id' => $visita->id,
                    'subcuenta_id' => $subcuenta->id,
                    'factura_id' => $factura->id,
                    'mesero_id' => $visita->mesero_id,
                    'turno_id' => $turno->id,
                    'monto_propina' => $propinaMonto,
                    'porcentaje' => (float)($validated['propina_porcentaje'] ?? 0.00),
                    'metodo_pago' => ($metodoPago === 'MIXTO') ? 'EFECTIVO' : $metodoPago,
                ]);
            }

            // 3. Impactar contadores del turno de caja
            $montoEfectivo = 0.0;
            $montoQr = 0.0;
            $montoTarjeta = 0.0;

            if ($metodoPago === 'EFECTIVO') {
                $montoEfectivo = $montoSubcuenta + $propinaMonto;
            } elseif ($metodoPago === 'QR') {
                $montoQr = $montoSubcuenta + $propinaMonto;
            } elseif ($metodoPago === 'TARJETA') {
                $montoTarjeta = $montoSubcuenta + $propinaMonto;
            } elseif ($metodoPago === 'MIXTO') {
                $montoEfectivo = (float)($validated['monto_mixto_efectivo'] ?? 0);
                $digital = (float)($validated['monto_mixto_digital'] ?? 0);
                if (($validated['segundo_metodo_pago'] ?? 'TARJETA') === 'QR') {
                    $montoQr = $digital;
                } else {
                    $montoTarjeta = $digital;
                }
            }

            $turno->total_ventas_efectivo = (float)$turno->total_ventas_efectivo + $montoEfectivo;
            $turno->total_ventas_qr = (float)$turno->total_ventas_qr + $montoQr;
            $turno->total_ventas_tarjeta = (float)$turno->total_ventas_tarjeta + $montoTarjeta;

            if ($tipoComprobante === 'FACTURA') {
                $turno->total_facturado = (float)$turno->total_facturado + $montoSubcuenta;
            } else {
                $turno->total_recibos = (float)$turno->total_recibos + $montoSubcuenta;
            }
            $turno->save();

            // 4. Marcar Subcuenta como COBRADA
            $subcuenta->estado = 'COBRADA';
            $subcuenta->monto_pagado = $montoSubcuenta;
            $subcuenta->factura_id = $factura->id;
            $subcuenta->save();

            // 5. Verificar si TODAS las subcuentas de la mesa están cobradas
            $subcuentasPendientes = SubcuentaVisitaModel::where('visita_id', $visita->id)
                ->where('estado', '!=', 'COBRADA')
                ->count();

            $mesaLiberada = false;
            if ($subcuentasPendientes === 0) {
                $visita->estado = 'COBRADA';
                $visita->fecha_cierre = now();
                $visita->save();

                // Mesa queda libre al cerrar visita
                $mesaLiberada = true;
            }

            return response()->json([
                'success' => true,
                'message' => "Subcuenta '{$subcuenta->nombre_comensal}' cobrada exitosamente",
                'data' => [
                    'subcuenta' => $subcuenta,
                    'factura' => $factura,
                    'propina' => $propinaMonto,
                    'mesa_liberada' => $mesaLiberada,
                ],
            ]);
        });
    }

    /**
     * Reporte de Propinas del Turno agrupado por Mesero (FrmPropinas RestoTech)
     */
    public function getReportePropinasTurno(Request $request, int $turnoId): JsonResponse
    {
        $propinas = PropinaModel::with('mesero')
            ->where('turno_id', $turnoId)
            ->get();

        $agrupado = $propinas->groupBy('mesero_id')->map(function ($items, $meseroId) {
            $mesero = $items->first()?->mesero;
            return [
                'mesero_id' => $meseroId,
                'mesero_nombre' => $mesero ? $mesero->name : 'Mesero General',
                'total_propinas' => round((float)$items->sum('monto_propina'), 2),
                'total_efectivo' => round((float)$items->where('metodo_pago', 'EFECTIVO')->sum('monto_propina'), 2),
                'total_digital' => round((float)$items->where('metodo_pago', '!=', 'EFECTIVO')->sum('monto_propina'), 2),
                'cantidad_mesas' => $items->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $agrupado,
            'total_general' => round((float)$propinas->sum('monto_propina'), 2),
        ]);
    }
}
