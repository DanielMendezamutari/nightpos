<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaOperacionHistorialModel;
use App\Infrastructure\Persistence\Eloquent\Models\SubcuentaVisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaDetalleModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MesaOperacionesController extends Controller
{
    /**
     * Traslada una cuenta completa a una mesa libre (frmCambiarMesa).
     */
    public function cambiarMesa(Request $request, int $mesaId): JsonResponse
    {
        $request->validate([
            'mesa_destino_id' => 'required|integer|exists:mesas,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        $mesaDestinoId = (int) $request->mesa_destino_id;
        if ($mesaId === $mesaDestinoId) {
            return response()->json([
                'message' => 'La mesa de origen y destino no pueden ser la misma.',
            ], 422);
        }

        $mesaOrigen = MesaModel::findOrFail($mesaId);
        $mesaDestino = MesaModel::findOrFail($mesaDestinoId);

        $visitaOrigen = VisitaModel::where('mesa_id', $mesaId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if (!$visitaOrigen) {
            return response()->json([
                'message' => "La mesa {$mesaOrigen->codigo} no tiene una cuenta activa para trasladar.",
            ], 422);
        }

        // Verificar si la mesa destino está ocupada
        $visitaDestino = VisitaModel::where('mesa_id', $mesaDestinoId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if ($visitaDestino) {
            return response()->json([
                'message' => "La mesa {$mesaDestino->codigo} ya tiene una cuenta activa.",
                'es_ocupada' => true,
                'mesa_destino_codigo' => $mesaDestino->codigo,
                'mesa_destino_nombre' => $mesaDestino->nombre,
                'visita_destino_id' => $visitaDestino->id,
            ], 409);
        }

        DB::beginTransaction();
        try {
            $usuario = auth()->user() ?? UserModel::first();
            $motivo = $request->input('motivo', 'Cambio solicitado por cliente / mesero');

            // Trasladar la visita a la nueva mesa
            $visitaOrigen->update([
                'mesa_id' => $mesaDestinoId,
                'notas' => ($visitaOrigen->notas ? $visitaOrigen->notas . ' | ' : '') . "Trasladado desde Mesa {$mesaOrigen->codigo}",
            ]);

            // Registrar historial de auditoría
            MesaOperacionHistorialModel::create([
                'tenant_id' => $visitaOrigen->tenant_id,
                'branch_id' => $visitaOrigen->branch_id,
                'tipo_operacion' => 'CAMBIO_MESA',
                'visita_id' => $visitaOrigen->id,
                'mesa_origen_id' => $mesaId,
                'mesa_destino_id' => $mesaDestinoId,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'detalles_json' => [
                    'mesa_origen' => $mesaOrigen->codigo,
                    'mesa_destino' => $mesaDestino->codigo,
                    'total_cuenta' => (float) $visitaOrigen->total,
                ],
            ]);

            DB::commit();

            $ticketAuditoria = "========================================\n" .
                "           CAMBIO DE MESA\n" .
                "========================================\n" .
                "Realizado por:  " . ($usuario->name ?? 'Usuario') . "\n" .
                "Mesa Original:  {$mesaOrigen->codigo} - {$mesaOrigen->nombre}\n" .
                "Nueva Mesa:     {$mesaDestino->codigo} - {$mesaDestino->nombre}\n" .
                "Fecha / Hora:   " . now()->format('d/m/Y H:i:s') . "\n" .
                "Total Cuenta:   Bs. " . number_format((float) $visitaOrigen->total, 2) . "\n" .
                "Motivo:         {$motivo}\n" .
                "========================================";

            return response()->json([
                'message' => "La cuenta de Mesa {$mesaOrigen->codigo} se cambió exitosamente a Mesa {$mesaDestino->codigo}.",
                'ticket_auditoria' => $ticketAuditoria,
                'mesa_origen' => $mesaOrigen,
                'mesa_destino' => $mesaDestino,
                'visita' => $visitaOrigen->fresh(['detalles']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al cambiar de mesa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fusiona/Junta dos cuentas de mesas ocupadas en una sola (JuntandoMesa).
     */
    public function juntarMesa(Request $request, int $mesaId): JsonResponse
    {
        $request->validate([
            'mesa_destino_id' => 'required|integer|exists:mesas,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        $mesaDestinoId = (int) $request->mesa_destino_id;
        if ($mesaId === $mesaDestinoId) {
            return response()->json([
                'message' => 'La mesa de origen y destino no pueden ser la misma.',
            ], 422);
        }

        $mesaOrigen = MesaModel::findOrFail($mesaId);
        $mesaDestino = MesaModel::findOrFail($mesaDestinoId);

        $visitaOrigen = VisitaModel::where('mesa_id', $mesaId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        $visitaDestino = VisitaModel::where('mesa_id', $mesaDestinoId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if (!$visitaOrigen) {
            return response()->json([
                'message' => "La mesa de origen {$mesaOrigen->codigo} no tiene una cuenta activa.",
            ], 422);
        }

        if (!$visitaDestino) {
            return response()->json([
                'message' => "La mesa de destino {$mesaDestino->codigo} no tiene una cuenta activa. Utilice Cambiar Mesa.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $usuario = auth()->user() ?? UserModel::first();
            $motivo = $request->input('motivo', 'Unión de cuentas solicitada por comensales');

            // Asegurar que la visita destino tenga una subcuenta principal
            $subcuentaPrincipalDestino = SubcuentaVisitaModel::firstOrCreate(
                [
                    'visita_id' => $visitaDestino->id,
                    'numero_subcuenta' => 1,
                ],
                [
                    'tenant_id' => $visitaDestino->tenant_id,
                    'branch_id' => $visitaDestino->branch_id,
                    'nombre_comensal' => 'Cuenta Principal',
                    'total' => (float) $visitaDestino->total,
                    'estado' => 'PENDIENTE',
                ]
            );

            // Transferir todos los detalles de visitaOrigen a visitaDestino
            VisitaDetalleModel::where('visita_id', $visitaOrigen->id)
                ->update([
                    'visita_id' => $visitaDestino->id,
                    'subcuenta_id' => $subcuentaPrincipalDestino->id,
                ]);

            // Recalcular total de visitaDestino
            $nuevoTotal = (float) VisitaDetalleModel::where('visita_id', $visitaDestino->id)
                ->where('estado', '!=', 'CANCELADO')
                ->sum('subtotal');

            $visitaDestino->update([
                'total' => $nuevoTotal,
                'personas' => $visitaDestino->personas + $visitaOrigen->personas,
                'notas' => ($visitaDestino->notas ? $visitaDestino->notas . ' | ' : '') . "Juntada con Mesa {$mesaOrigen->codigo}",
            ]);

            // Actualizar subcuenta principal
            $subcuentaPrincipalDestino->update(['total' => $nuevoTotal]);

            // Cerrar visitaOrigen como FUSIONADA (libera la mesa origen)
            $visitaOrigen->update([
                'estado' => 'ANULADA',
                'fecha_cierre' => now(),
                'notas' => ($visitaOrigen->notas ? $visitaOrigen->notas . ' | ' : '') . "FUSIONADA con Mesa {$mesaDestino->codigo}",
            ]);

            // Registrar auditoría
            MesaOperacionHistorialModel::create([
                'tenant_id' => $visitaDestino->tenant_id,
                'branch_id' => $visitaDestino->branch_id,
                'tipo_operacion' => 'JUNTAR_MESA',
                'visita_id' => $visitaDestino->id,
                'mesa_origen_id' => $mesaId,
                'mesa_destino_id' => $mesaDestinoId,
                'usuario_id' => $usuario->id,
                'motivo' => $motivo,
                'detalles_json' => [
                    'mesa_origen' => $mesaOrigen->codigo,
                    'mesa_destino' => $mesaDestino->codigo,
                    'total_consolidado' => $nuevoTotal,
                ],
            ]);

            DB::commit();

            $ticketAuditoria = "========================================\n" .
                "      JUNTANDO MESAS (UNION DE CUENTAS)\n" .
                "========================================\n" .
                "Realizado por:      " . ($usuario->name ?? 'Usuario') . "\n" .
                "Mesa Original:      {$mesaOrigen->codigo}\n" .
                "Se juntó con:       {$mesaDestino->codigo}\n" .
                "Fecha / Hora:       " . now()->format('d/m/Y H:i:s') . "\n" .
                "Total Consolidado:  Bs. " . number_format($nuevoTotal, 2) . "\n" .
                "Comensales Totales: " . $visitaDestino->personas . "\n" .
                "Motivo:             {$motivo}\n" .
                "========================================";

            return response()->json([
                'message' => "Se juntaron las cuentas de Mesa {$mesaOrigen->codigo} y Mesa {$mesaDestino->codigo} exitosamente.",
                'ticket_auditoria' => $ticketAuditoria,
                'mesa_origen' => $mesaOrigen,
                'mesa_destino' => $mesaDestino,
                'visita_consolidada' => $visitaDestino->fresh(['detalles']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al juntar mesas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Lista pedidos activos sin mesa física o mostrador / para llevar / delivery (frmSinMesa).
     */
    public function getPedidosSinMesa(Request $request): JsonResponse
    {
        $pedidos = VisitaModel::with(['detalles.producto', 'mesero'])
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->where(function ($q) {
                $q->whereNull('mesa_id')
                  ->orWhereIn('tipo_despacho', ['LLEVAR', 'MOSTRADOR', 'DELIVERY', 'BARRA']);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($visita) {
                $minutos = $visita->created_at ? (int) $visita->created_at->diffInMinutes(now()) : 0;
                return [
                    'id' => $visita->id,
                    'tipo_despacho' => $visita->tipo_despacho ?? 'LLEVAR',
                    'cliente_nombre' => $visita->cliente_nombre ?? $visita->nombre_para_llevar ?? 'Cliente General',
                    'telefono_cliente' => $visita->telefono_cliente,
                    'direccion_envio' => $visita->direccion_envio,
                    'personas' => $visita->personas,
                    'estado' => $visita->estado,
                    'total' => (float) $visita->total,
                    'minutos_transcurridos' => $minutos,
                    'cant_items' => $visita->detalles->where('estado', '!=', 'CANCELADO')->count(),
                    'detalles' => $visita->detalles,
                    'mesero' => $visita->mesero,
                    'created_at' => $visita->created_at,
                ];
            });

        return response()->json($pedidos);
    }

    /**
     * Crea un pedido sin mesa física (Para Llevar, Mostrador, Delivery, Barra).
     */
    public function crearPedidoSinMesa(Request $request): JsonResponse
    {
        $request->validate([
            'tipo_despacho' => 'required|string|in:LLEVAR,MOSTRADOR,DELIVERY,BARRA',
            'cliente_nombre' => 'required|string|max:150',
            'telefono_cliente' => 'nullable|string|max:30',
            'direccion_envio' => 'nullable|string|max:500',
            'notas' => 'nullable|string|max:500',
            'mesero_id' => 'nullable|uuid|exists:users,id',
        ]);

        $usuario = auth()->user() ?? UserModel::first();
        $meseroId = $request->mesero_id ?? $usuario->id;

        $visita = VisitaModel::create([
            'tenant_id' => $usuario->tenant_id,
            'branch_id' => $usuario->branch_id,
            'mesa_id' => null,
            'mesero_id' => $meseroId,
            'tipo_despacho' => $request->tipo_despacho,
            'cliente_nombre' => $request->cliente_nombre,
            'nombre_para_llevar' => $request->cliente_nombre,
            'telefono_cliente' => $request->telefono_cliente,
            'direccion_envio' => $request->direccion_envio,
            'personas' => 1,
            'estado' => 'ABIERTA',
            'imprimio_cuenta' => false,
            'fecha_apertura' => now(),
            'total' => 0.00,
            'notas' => $request->notas,
        ]);

        return response()->json([
            'message' => "Pedido de {$request->tipo_despacho} para '{$request->cliente_nombre}' creado exitosamente.",
            'visita' => $visita,
        ], 201);
    }

    /**
     * Asigna un pedido sin mesa a una mesa física libre (PonerCodigoEnMesa).
     */
    public function asignarMesaAPedidoSinMesa(Request $request, int $visitaId): JsonResponse
    {
        $request->validate([
            'mesa_destino_id' => 'required|integer|exists:mesas,id',
        ]);

        $visita = VisitaModel::findOrFail($visitaId);
        $mesaDestino = MesaModel::findOrFail($request->mesa_destino_id);

        // Verificar que la mesa destino esté libre
        $visitaEnMesa = VisitaModel::where('mesa_id', $mesaDestino->id)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if ($visitaEnMesa) {
            return response()->json([
                'message' => "La mesa {$mesaDestino->codigo} ya tiene una cuenta activa. Seleccione una mesa libre.",
            ], 422);
        }

        DB::beginTransaction();
        try {
            $usuario = auth()->user() ?? UserModel::first();

            $visita->update([
                'mesa_id' => $mesaDestino->id,
                'tipo_despacho' => 'MESA',
                'notas' => ($visita->notas ? $visita->notas . ' | ' : '') . "Asignado a Mesa {$mesaDestino->codigo}",
            ]);

            MesaOperacionHistorialModel::create([
                'tenant_id' => $visita->tenant_id,
                'branch_id' => $visita->branch_id,
                'tipo_operacion' => 'ASIGNAR_MESA',
                'visita_id' => $visita->id,
                'mesa_origen_id' => null,
                'mesa_destino_id' => $mesaDestino->id,
                'usuario_id' => $usuario->id,
                'motivo' => "Comensal sin mesa ubicado en Mesa {$mesaDestino->codigo}",
                'detalles_json' => [
                    'cliente' => $visita->cliente_nombre,
                    'mesa_destino' => $mesaDestino->codigo,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => "Pedido asignado exitosamente a Mesa {$mesaDestino->codigo}.",
                'visita' => $visita->fresh(['detalles']),
                'mesa' => $mesaDestino,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al asignar mesa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reasigna el mesero de una mesa activa (frmSeleccionarMesero).
     */
    public function reasignarMesero(Request $request, int $mesaId): JsonResponse
    {
        $request->validate([
            'mesero_id' => 'required|uuid|exists:users,id',
            'motivo' => 'nullable|string|max:255',
        ]);

        $mesa = MesaModel::findOrFail($mesaId);
        $visita = VisitaModel::where('mesa_id', $mesaId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->firstOrFail();

        $nuevoMesero = UserModel::findOrFail($request->mesero_id);
        $usuario = auth()->user() ?? $nuevoMesero;

        $visita->update([
            'mesero_id' => $nuevoMesero->id,
        ]);

        MesaOperacionHistorialModel::create([
            'tenant_id' => $visita->tenant_id,
            'branch_id' => $visita->branch_id,
            'tipo_operacion' => 'REASIGNAR_MESERO',
            'visita_id' => $visita->id,
            'mesa_origen_id' => $mesaId,
            'mesa_destino_id' => $mesaId,
            'usuario_id' => $usuario->id,
            'motivo' => $request->input('motivo', 'Reasignación de mesero'),
            'detalles_json' => [
                'nuevo_mesero' => $nuevoMesero->name,
            ],
        ]);

        return response()->json([
            'message' => "Mesero reasignado a {$nuevoMesero->name} en Mesa {$mesa->codigo}.",
            'visita' => $visita->fresh(['mesero']),
        ]);
    }
    /**
     * Obtiene el detalle completo de una visita (pedidos con o sin mesa).
     */
    public function getVisita(int $visitaId): JsonResponse
    {
        $visita = VisitaModel::with(['detalles.producto', 'mesero', 'mesa'])->findOrFail($visitaId);

        return response()->json([
            'success' => true,
            'visita' => $visita,
        ]);
    }
}
