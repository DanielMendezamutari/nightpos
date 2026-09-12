<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KdsController extends Controller
{
    /**
     * Obtener comandas activas para el monitor de cocina / barra.
     * Replica frmKitchenDisplay de RestoTech:
     * WHERE DetalleCuenta.Terminado is null
     */
    public function getTickets(Request $request): JsonResponse
    {
        $estacion = strtoupper((string) $request->query('estacion', 'TODAS'));

        $visitas = VisitaModel::with(['mesa.salon', 'mesero', 'detalles.producto'])
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->whereHas('detalles', function ($query) use ($estacion) {
                $query->where('estado', '!=', 'CANCELADO')
                      ->whereNull('terminado_at');
                if ($estacion !== 'TODAS') {
                    $query->where('estacion_cocina', $estacion);
                }
            })
            ->orderBy('fecha_apertura', 'asc')
            ->get();

        $tickets = [];
        $totalMinutes = 0;
        $demoradosCount = 0;

        foreach ($visitas as $visita) {
            $itemsQuery = $visita->detalles
                ->where('estado', '!=', 'CANCELADO');

            if ($estacion !== 'TODAS') {
                $itemsQuery = $itemsQuery->where('estacion_cocina', $estacion);
            }

            // Si todos los items de esta estación ya están terminados, omitir
            $pendientes = $itemsQuery->whereNull('terminado_at');
            if ($pendientes->isEmpty()) {
                continue;
            }

            // Calcular tiempo transcurrido desde el ítem más antiguo pendiente
            $primerItem = $pendientes->sortBy('iniciado_at')->first();
            $horaInicio = $primerItem->iniciado_at ? Carbon::parse($primerItem->iniciado_at) : Carbon::parse($visita->fecha_apertura);
            $minutosTranscurridos = (int) max(0, $horaInicio->diffInMinutes(now()));

            $totalMinutes += $minutosTranscurridos;

            // Semáforo RestoTech
            if ($minutosTranscurridos >= 20) {
                $urgencia = 'DEMORADO'; // Rojo
                $demoradosCount++;
            } elseif ($minutosTranscurridos >= 10) {
                $urgencia = 'ALERTA'; // Amarillo
            } else {
                $urgencia = 'NORMAL'; // Verde
            }

            $itemsData = $itemsQuery->map(function ($det) {
                return [
                    'id' => $det->id,
                    'producto_id' => $det->producto_id,
                    'producto_nombre' => $det->producto_nombre,
                    'cantidad' => (float) $det->cantidad,
                    'observaciones' => $det->observaciones,
                    'estacion_cocina' => $det->estacion_cocina ?? 'COCINA',
                    'estado' => $det->estado,
                    'iniciado_at' => $det->iniciado_at ? Carbon::parse($det->iniciado_at)->format('H:i:s') : null,
                    'terminado_at' => $det->terminado_at ? Carbon::parse($det->terminado_at)->format('H:i:s') : null,
                    'es_terminado' => !is_null($det->terminado_at),
                ];
            })->values();

            $tickets[] = [
                'visita_id' => $visita->id,
                'mesa_id' => $visita->mesa_id,
                'mesa_codigo' => $visita->mesa->codigo ?? 'MESA',
                'mesa_nombre' => $visita->mesa->nombre ?? "Mesa #{$visita->mesa_id}",
                'salon_nombre' => $visita->mesa->salon->nombre ?? 'Salón Principal',
                'mesero_nombre' => $visita->mesero->name ?? 'Mozo General',
                'personas' => $visita->personas ?? 2,
                'notas' => $visita->notas,
                'hora_pedido' => $horaInicio->format('H:i:s'),
                'minutos_transcurridos' => $minutosTranscurridos,
                'categoria_urgencia' => $urgencia,
                'total_items' => $itemsData->count(),
                'items_pendientes' => $itemsData->where('es_terminado', false)->count(),
                'items' => $itemsData,
            ];
        }

        $count = count($tickets);
        $avgMinutes = $count > 0 ? round($totalMinutes / $count, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => $tickets,
            'metrics' => [
                'total_activos' => $count,
                'demorados' => $demoradosCount,
                'tiempo_promedio_min' => $avgMinutes,
                'estacion' => $estacion,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Cambiar estado de un ítem individual (EN_PREPARACION / LISTO)
     */
    public function cambiarEstadoItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'estado' => 'required|string|in:EN_PREPARACION,LISTO,SERVIDO',
        ]);

        $detalle = VisitaDetalleModel::findOrFail($id);

        if ($validated['estado'] === 'LISTO' || $validated['estado'] === 'SERVIDO') {
            $detalle->terminado_at = now();
            $detalle->estado = 'LISTO';
        } else {
            $detalle->terminado_at = null;
            if (!$detalle->iniciado_at) {
                $detalle->iniciado_at = now();
            }
            $detalle->estado = 'EN_PREPARACION';
        }

        $detalle->save();

        return response()->json([
            'success' => true,
            'message' => "Ítem #{$id} actualizado a {$detalle->estado}",
            'data' => [
                'id' => $detalle->id,
                'estado' => $detalle->estado,
                'iniciado_at' => $detalle->iniciado_at,
                'terminado_at' => $detalle->terminado_at,
                'es_terminado' => !is_null($detalle->terminado_at),
            ],
        ]);
    }

    /**
     * Despachar ticket completo de una mesa / visita (FoodIsReady).
     * Marca como terminado_at = now() todos los ítems pendientes de la estación.
     */
    public function despacharTicket(Request $request, int $visitaId): JsonResponse
    {
        $estacion = strtoupper((string) $request->input('estacion', 'TODAS'));

        $query = VisitaDetalleModel::where('visita_id', $visitaId)
            ->where('estado', '!=', 'CANCELADO')
            ->whereNull('terminado_at');

        if ($estacion !== 'TODAS') {
            $query->where('estacion_cocina', $estacion);
        }

        $updatedCount = $query->update([
            'terminado_at' => now(),
            'estado' => 'LISTO',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comanda despachada exitosamente (FoodIsReady)',
            'data' => [
                'visita_id' => $visitaId,
                'estacion' => $estacion,
                'items_despachados' => $updatedCount,
                'terminado_at' => now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Historial de últimas comandas despachadas con tiempo de entrega real.
     * Replica: DateDiff(minute, hora, Terminado) as TiempoEntrega
     */
    public function getHistorial(Request $request): JsonResponse
    {
        $visitas = VisitaModel::with(['mesa.salon', 'mesero', 'detalles'])
            ->whereHas('detalles', function ($query) {
                $query->whereNotNull('terminado_at');
            })
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        $historial = [];

        foreach ($visitas as $visita) {
            $despachados = $visita->detalles->whereNotNull('terminado_at');
            if ($despachados->isEmpty()) {
                continue;
            }

            $ultimoTerminado = $despachados->sortByDesc('terminado_at')->first();
            $primerIniciado = $despachados->sortBy('iniciado_at')->first();

            $inicio = $primerIniciado && $primerIniciado->iniciado_at ? Carbon::parse($primerIniciado->iniciado_at) : Carbon::parse($visita->fecha_apertura);
            $fin = Carbon::parse($ultimoTerminado->terminado_at);
            $tiempoEntrega = (int) max(0, $inicio->diffInMinutes($fin));

            $historial[] = [
                'visita_id' => $visita->id,
                'mesa_codigo' => $visita->mesa->codigo ?? 'MESA',
                'mesa_nombre' => $visita->mesa->nombre ?? "Mesa #{$visita->mesa_id}",
                'salon_nombre' => $visita->mesa->salon->nombre ?? 'Salón',
                'mesero_nombre' => $visita->mesero->name ?? 'Mozo',
                'hora_despacho' => $fin->format('H:i:s'),
                'tiempo_entrega_min' => $tiempoEntrega,
                'items_count' => $despachados->count(),
                'items' => $despachados->map(fn($d) => [
                    'id' => $d->id,
                    'producto_nombre' => $d->producto_nombre,
                    'cantidad' => (float) $d->cantidad,
                    'estacion_cocina' => $d->estacion_cocina,
                ])->values(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $historial,
        ]);
    }

    /**
     * Revertir despacho accidental de una comanda (volver a poner en preparación).
     */
    public function revertirDespacho(Request $request, int $visitaId): JsonResponse
    {
        $updatedCount = VisitaDetalleModel::where('visita_id', $visitaId)
            ->whereNotNull('terminado_at')
            ->update([
                'terminado_at' => null,
                'estado' => 'EN_PREPARACION',
            ]);

        return response()->json([
            'success' => true,
            'message' => "Comanda de la visita #{$visitaId} devuelta a cocina",
            'data' => [
                'visita_id' => $visitaId,
                'items_restaurados' => $updatedCount,
            ],
        ]);
    }
}
