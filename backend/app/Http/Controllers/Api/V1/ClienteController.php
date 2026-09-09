<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteModel;
use App\Infrastructure\Persistence\Eloquent\Models\ClienteMovimientoModel;
use App\Infrastructure\Persistence\Eloquent\Models\AnticipoModel;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoCajaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Listar clientes con filtros tÃ¡ctiles de RestoTech (BÃºsqueda, Deudores, CumpleaÃ±eros)
     */
    public function index(Request $request): JsonResponse
    {
        $query = ClienteModel::where('activo', true);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhere('ci_nit', 'like', "%{$search}%")
                  ->orWhere('celular', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('solo_deudores')) {
            $query->where('saldo_deuda', '>', 0);
        }

        if ($mes = $request->input('cumpleaneros_mes')) {
            $mesStr = str_pad($mes, 2, '0', STR_PAD_LEFT);
            if (DB::connection()->getDriverName() === 'sqlite') {
                $query->whereRaw("strftime('%m', cumpleanos) = ?", [$mesStr]);
            } else {
                $query->whereMonth('cumpleanos', $mes);
            }
        }

        $clientes = $query->orderBy('nombre')->get();

        return response()->json([
            'success' => true,
            'data' => $clientes,
        ]);
    }

    /**
     * Registrar nuevo cliente en el directorio
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'ci_nit' => 'nullable|string|max:30',
            'tipo_documento' => 'nullable|string|in:CI,NIT,CEX,PASAPORTE',
            'razon_social' => 'nullable|string|max:150',
            'celular' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'correo' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'cumpleanos' => 'nullable|date',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'limite_credito' => 'nullable|numeric|min:0',
            'permite_credito' => 'nullable|boolean',
            'comentarios' => 'nullable|string',
        ]);

        $cliente = ClienteModel::create([
            'nombre' => $validated['nombre'],
            'apellidos' => $validated['apellidos'] ?? null,
            'ci_nit' => $validated['ci_nit'] ?? '0',
            'tipo_documento' => $validated['tipo_documento'] ?? 'NIT',
            'razon_social' => $validated['razon_social'] ?? $validated['nombre'],
            'celular' => $validated['celular'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'correo' => $validated['correo'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
            'cumpleanos' => $validated['cumpleanos'] ?? null,
            'descuento_porcentaje' => $validated['descuento_porcentaje'] ?? 0.00,
            'limite_credito' => $validated['limite_credito'] ?? 0.00,
            'permite_credito' => $validated['permite_credito'] ?? false,
            'comentarios' => $validated['comentarios'] ?? null,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente registrado exitosamente',
            'data' => $cliente,
        ], 201);
    }

    /**
     * Ver ficha de cliente con historial y anticipos
     */
    public function show(int $id): JsonResponse
    {
        $cliente = ClienteModel::with(['movimientos', 'anticipos'])->find($id);

        if (!$cliente) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cliente,
        ]);
    }

    /**
     * Actualizar datos del cliente
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = ClienteModel::find($id);
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'apellidos' => 'nullable|string|max:100',
            'ci_nit' => 'nullable|string|max:30',
            'tipo_documento' => 'nullable|string|in:CI,NIT,CEX,PASAPORTE',
            'razon_social' => 'nullable|string|max:150',
            'celular' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:30',
            'correo' => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:200',
            'cumpleanos' => 'nullable|date',
            'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
            'limite_credito' => 'nullable|numeric|min:0',
            'permite_credito' => 'nullable|boolean',
            'comentarios' => 'nullable|string',
        ]);

        $cliente->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'data' => $cliente,
        ]);
    }

    /**
     * Desactivar cliente
     */
    public function destroy(int $id): JsonResponse
    {
        $cliente = ClienteModel::find($id);
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        $cliente->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente desactivado correctamente',
        ]);
    }

    /**
     * Registrar abono o pago a cuenta corriente (pago de deuda)
     */
    public function registrarAbono(Request $request, int $id): JsonResponse
    {
        $cliente = ClienteModel::find($id);
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.50',
            'metodo_pago' => 'nullable|string|in:EFECTIVO,TARJETA,QR',
            'referencia' => 'nullable|string|max:150',
        ]);

        $monto = (float) $validated['monto'];
        $saldoAnterior = (float) $cliente->saldo_deuda;
        $saldoNuevo = max(0, $saldoAnterior - $monto);

        $turnoActivo = TurnoModel::where('estado', 'ABIERTO')->latest()->first();

        DB::transaction(function () use ($cliente, $monto, $saldoAnterior, $saldoNuevo, $turnoActivo, $validated) {
            $cliente->update(['saldo_deuda' => $saldoNuevo]);

            ClienteMovimientoModel::create([
                'cliente_id' => $cliente->id,
                'turno_id' => $turnoActivo?->id,
                'tipo' => 'ABONO_PAGO',
                'monto' => $monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
                'referencia' => $validated['referencia'] ?? 'Abono a Cuenta Corriente',
            ]);

            if ($turnoActivo) {
                MovimientoCajaModel::create([
                    'tenant_id' => $turnoActivo->tenant_id,
                    'branch_id' => $turnoActivo->branch_id,
                    'turno_id' => $turnoActivo->id,
                    'usuario_id' => $turnoActivo->cajero_id,
                    'tipo' => 'INGRESO',
                    'monto' => $monto,
                    'motivo' => 'Abono Deuda Cliente: ' . $cliente->nombre_completo,
                    'observaciones' => $validated['referencia'] ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Abono registrado con Ã©xito',
            'data' => [
                'cliente_id' => $cliente->id,
                'monto_abonado' => $monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
            ],
        ]);
    }

    /**
     * Registrar anticipo para eventos o reservas
     */
    public function registrarAnticipo(Request $request, int $id): JsonResponse
    {
        $cliente = ClienteModel::find($id);
        if (!$cliente) {
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
        }

        $validated = $request->validate([
            'monto' => 'required|numeric|min:1.00',
            'concepto' => 'nullable|string|max:150',
            'visita_id' => 'nullable|integer|exists:visitas,id',
        ]);

        $monto = (float) $validated['monto'];
        $turnoActivo = TurnoModel::where('estado', 'ABIERTO')->latest()->first();

        $anticipo = DB::transaction(function () use ($cliente, $monto, $turnoActivo, $validated) {
            $ant = AnticipoModel::create([
                'cliente_id' => $cliente->id,
                'turno_id' => $turnoActivo?->id,
                'visita_id' => $validated['visita_id'] ?? null,
                'monto_inicial' => $monto,
                'saldo_disponible' => $monto,
                'estado' => 'ACTIVO',
                'concepto' => $validated['concepto'] ?? 'Anticipo Reserva / Evento',
            ]);

            if ($turnoActivo) {
                MovimientoCajaModel::create([
                    'tenant_id' => $turnoActivo->tenant_id,
                    'branch_id' => $turnoActivo->branch_id,
                    'turno_id' => $turnoActivo->id,
                    'usuario_id' => $turnoActivo->cajero_id,
                    'tipo' => 'INGRESO',
                    'monto' => $monto,
                    'motivo' => 'Anticipo Reserva: ' . $cliente->nombre_completo,
                    'observaciones' => $validated['concepto'] ?? 'Anticipo Reserva / Evento',
                ]);
            }

            return $ant;
        });

        return response()->json([
            'success' => true,
            'message' => 'Anticipo registrado con Ã©xito',
            'data' => $anticipo,
        ], 201);
    }

    /**
     * Obtener anticipos activos con saldo disponible
     */
    public function anticiposDisponibles(int $id): JsonResponse
    {
        $anticipos = AnticipoModel::where('cliente_id', $id)
            ->where('estado', 'ACTIVO')
            ->where('saldo_disponible', '>', 0)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $anticipos,
        ]);
    }
}