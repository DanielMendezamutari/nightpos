<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Mesa\Entities\Mesa;
use App\Domain\Mesa\Enums\MesaEstado;
use App\Domain\Mesa\Repositories\MesaRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentMesaRepository implements MesaRepositoryInterface
{
    public function getMesasBySalon(int $salonId): array
    {
        $mesas = MesaModel::where('salon_id', $salonId)
            ->where('activo', true)
            ->with(['visitaActiva.mesero', 'visitaActiva.detalles'])
            ->orderByRaw('CAST(codigo AS UNSIGNED), codigo')
            ->get();

        return $mesas->map(fn (MesaModel $model) => $this->toDomain($model))->all();
    }

    public function findById(int $id): ?Mesa
    {
        $model = MesaModel::with(['visitaActiva.mesero', 'visitaActiva.detalles'])->find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function abrirMesa(int $mesaId, string $meseroId, int $personas, ?string $clienteNombre, ?string $notas): int
    {
        return DB::transaction(function () use ($mesaId, $meseroId, $personas, $clienteNombre, $notas) {
            $mesa = MesaModel::with('salon')->findOrFail($mesaId);

            // Verificar si ya tiene visita activa
            $visitaExistente = VisitaModel::where('mesa_id', $mesaId)
                ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
                ->first();

            if ($visitaExistente) {
                throw new \RuntimeException('La mesa ya tiene una cuenta abierta');
            }

            $visita = VisitaModel::create([
                'tenant_id' => $mesa->salon->tenant_id,
                'branch_id' => $mesa->salon->branch_id,
                'mesa_id' => $mesaId,
                'mesero_id' => $meseroId,
                'cliente_nombre' => $clienteNombre ?: 'Cliente Ocasional',
                'personas' => max(1, $personas),
                'estado' => 'ABIERTA',
                'imprimio_cuenta' => false,
                'fecha_apertura' => now(),
                'total' => 0.00,
                'notas' => $notas,
            ]);

            return $visita->id;
        });
    }

    public function cambiarMesa(int $mesaOrigenId, int $mesaDestinoId): bool
    {
        return DB::transaction(function () use ($mesaOrigenId, $mesaDestinoId) {
            $visitaOrigen = VisitaModel::where('mesa_id', $mesaOrigenId)
                ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
                ->first();

            if (!$visitaOrigen) {
                throw new \RuntimeException('La mesa de origen no tiene una cuenta activa para mover');
            }

            $visitaDestino = VisitaModel::where('mesa_id', $mesaDestinoId)
                ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
                ->first();

            if ($visitaDestino) {
                throw new \RuntimeException('La mesa de destino ya se encuentra ocupada');
            }

            $visitaOrigen->mesa_id = $mesaDestinoId;
            $visitaOrigen->save();

            return true;
        });
    }

    public function solicitarPrecuenta(int $mesaId): bool
    {
        $visita = VisitaModel::where('mesa_id', $mesaId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if (!$visita) {
            throw new \RuntimeException('No hay cuenta activa en esta mesa');
        }

        $visita->estado = 'PRECUENTA';
        $visita->imprimio_cuenta = true;
        $visita->save();

        return true;
    }

    public function liberarMesa(int $mesaId): bool
    {
        $visita = VisitaModel::where('mesa_id', $mesaId)
            ->whereIn('estado', ['ABIERTA', 'PRECUENTA'])
            ->first();

        if (!$visita) {
            return true; // Ya está libre
        }

        $visita->estado = 'COBRADA';
        $visita->fecha_cierre = now();
        $visita->save();

        return true;
    }

    private function toDomain(MesaModel $model): Mesa
    {
        $visita = $model->visitaActiva;

        $estado = MesaEstado::LIBRE;
        if ($visita) {
            $estado = match ($visita->estado) {
                'PRECUENTA' => MesaEstado::PRECUENTA,
                default => MesaEstado::OCUPADA,
            };
        }

        $fechaApertura = null;
        if ($visita && $visita->fecha_apertura) {
            $fechaApertura = new DateTimeImmutable($visita->fecha_apertura->toIso8601String());
        }

        return new Mesa(
            id: $model->id,
            salonId: $model->salon_id,
            codigo: $model->codigo,
            nombre: $model->nombre,
            capacidad: (int) $model->capacidad,
            posicionX: (int) $model->posicion_x,
            posicionY: (int) $model->posicion_y,
            ancho: (int) $model->ancho,
            alto: (int) $model->alto,
            forma: (string) $model->forma,
            activo: (bool) $model->activo,
            estado: $estado,
            visitaId: $visita?->id,
            meseroNombre: $visita?->mesero?->name,
            clienteNombre: $visita?->cliente_nombre,
            personas: (int) ($visita?->personas ?? 0),
            totalConsumo: (float) ($visita?->total ?? 0.0),
            fechaApertura: $fechaApertura,
            imprimioCuenta: (bool) ($visita?->imprimio_cuenta ?? false),
        );
    }
}