<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\CompraModel;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;
use App\Infrastructure\Persistence\Eloquent\Models\GastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\PropinaModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    private function parseRangoFechas(Request $request): array
    {
        $fechaDesde = $request->query('fecha_desde')
            ? Carbon::parse($request->query('fecha_desde'))->startOfDay()
            : Carbon::now()->startOfDay();

        $fechaHasta = $request->query('fecha_hasta')
            ? Carbon::parse($request->query('fecha_hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        return [$fechaDesde, $fechaHasta];
    }

    public function getResumenVentas(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->parseRangoFechas($request);

        $query = FacturaModel::whereBetween('created_at', [$desde, $hasta])
            ->where('estado', '!=', 'ANULADA');

        if ($request->has('turno_id') && $request->query('turno_id')) {
            $query->where('turno_id', $request->query('turno_id'));
        }

        $facturas = $query->get();

        $totalVentas = (float)$facturas->sum('monto_total');
        $totalTransacciones = $facturas->count();
        $ticketPromedio = $totalTransacciones > 0 ? round($totalVentas / $totalTransacciones, 2) : 0.0;

        $efectivo = (float)$facturas->sum(function ($f) {
            if ($f->monto_efectivo > 0) return (float)$f->monto_efectivo;
            return strtoupper((string)$f->metodo_pago) === 'EFECTIVO' ? (float)$f->monto_total : 0.0;
        });

        $qr = (float)$facturas->sum(function ($f) {
            if ($f->monto_qr > 0) return (float)$f->monto_qr;
            return strtoupper((string)$f->metodo_pago) === 'QR' ? (float)$f->monto_total : 0.0;
        });

        $tarjeta = (float)$facturas->sum(function ($f) {
            if ($f->monto_tarjeta > 0) return (float)$f->monto_tarjeta;
            return strtoupper((string)$f->metodo_pago) === 'TARJETA' ? (float)$f->monto_total : 0.0;
        });

        $otros = max(0.0, round($totalVentas - ($efectivo + $qr + $tarjeta), 2));

        $totalFacturado = (float)$facturas->where('tipo_comprobante', 'FACTURA')->sum('monto_total');
        $totalRecibos = (float)$facturas->where('tipo_comprobante', 'RECIBO')->sum('monto_total');

        // Ventas por dÃ­a para grÃ¡ficas
        $ventasPorDia = FacturaModel::select(
            DB::raw('DATE(created_at) as fecha'),
            DB::raw('COUNT(*) as transacciones'),
            DB::raw('SUM(monto_total) as total')
        )
            ->whereBetween('created_at', [$desde, $hasta])
            ->where('estado', '!=', 'ANULADA')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_ventas' => $totalVentas,
                'total_transacciones' => $totalTransacciones,
                'ticket_promedio' => $ticketPromedio,
                'total_facturado' => $totalFacturado,
                'total_recibos' => $totalRecibos,
                'desglose_pagos' => [
                    'efectivo' => $efectivo,
                    'qr' => $qr,
                    'tarjeta' => $tarjeta,
                    'otros' => $otros,
                ],
                'ventas_por_dia' => $ventasPorDia,
            ],
        ]);
    }

    public function getTopProductos(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->parseRangoFechas($request);

        $limit = (int)$request->query('limit', 15);

        $top = VisitaDetalleModel::select(
            'producto_id',
            'producto_nombre as nombre',
            DB::raw('SUM(cantidad) as total_cantidad'),
            DB::raw('SUM(subtotal) as total_recaudado')
        )
            ->whereBetween('created_at', [$desde, $hasta])
            ->where('estado', '!=', 'ANULADO')
            ->groupBy('producto_id', 'producto_nombre')
            ->orderByDesc('total_cantidad')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'producto_id' => $item->producto_id,
                    'nombre' => $item->nombre,
                    'total_cantidad' => (int)$item->total_cantidad,
                    'total_recaudado' => (float)$item->total_recaudado,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $top,
        ]);
    }

    public function getRendimientoMeseros(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->parseRangoFechas($request);

        $meseros = UserModel::whereIn('role', ['garzon', 'mesero', 'cajero', 'admin'])->get();

        $data = [];

        foreach ($meseros as $m) {
            $visitas = VisitaModel::where('mesero_id', $m->id)
                ->whereBetween('created_at', [$desde, $hasta])
                ->where('estado', 'CERRADA')
                ->get();

            $totalVentas = (float)$visitas->sum('monto_total');
            $totalMesas = $visitas->count();

            $propinas = (float)PropinaModel::where('mesero_id', $m->id)
                ->whereBetween('created_at', [$desde, $hasta])
                ->sum('monto_propina');

            if ($totalMesas > 0 || $propinas > 0) {
                $data[] = [
                    'id' => $m->id,
                    'nombre' => $m->name,
                    'rol' => $m->role,
                    'total_mesas_atendidas' => $totalMesas,
                    'total_ventas' => $totalVentas,
                    'total_propinas' => (float)$propinas,
                ];
            }
        }

        if (empty($data)) {
            $usuarios = UserModel::all();
            foreach ($usuarios as $u) {
                $propinas = (float)PropinaModel::where('mesero_id', $u->id)
                    ->whereBetween('created_at', [$desde, $hasta])
                    ->sum('monto_propina');
                if ($propinas > 0) {
                    $data[] = [
                        'id' => $u->id,
                        'nombre' => $u->name,
                        'rol' => $u->role,
                        'total_mesas_atendidas' => 0,
                        'total_ventas' => 0.0,
                        'total_propinas' => (float)$propinas,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getBalanceFinanciero(Request $request): JsonResponse
    {
        [$desde, $hasta] = $this->parseRangoFechas($request);

        $totalVentas = (float)FacturaModel::whereBetween('created_at', [$desde, $hasta])
            ->where('estado', '!=', 'ANULADA')
            ->sum('monto_total');

        $totalGastos = (float)GastoModel::whereBetween('created_at', [$desde, $hasta])
            ->whereNotIn('estado', ['anulado', 'ANULADO', 'ANULADA'])
            ->sum('monto');

        $totalCompras = (float)CompraModel::whereBetween('created_at', [$desde, $hasta])
            ->whereNotIn('estado', ['anulado', 'ANULADO', 'ANULADA'])
            ->sum('monto_total');

        $utilidadBruta = round($totalVentas - ($totalGastos + $totalCompras), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'total_ingresos_ventas' => $totalVentas,
                'total_gastos_caja' => $totalGastos,
                'total_compras_insumos' => $totalCompras,
                'utilidad_operativa_bruta' => $utilidadBruta,
            ],
        ]);
    }
}