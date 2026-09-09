<?php

declare(strict_types=1);

namespace App\Domain\Caja\Repositories;

use App\Domain\Caja\Entities\Turno;
use App\Domain\Caja\Entities\MovimientoCaja;

interface CajaRepositoryInterface
{
    public function getTurnoActivo(string $tenantId, string $branchId): ?Turno;
    public function getTurnoById(int $id): ?Turno;
    public function abrirTurno(string $tenantId, string $branchId, string $cajeroId, float $montoInicialBs, float $montoInicialUsd, ?string $observaciones = null): Turno;
    public function cerrarTurno(int $turnoId, float $montoFinalBs, float $montoFinalUsd, ?string $observaciones = null): Turno;
    public function registrarMovimiento(string $tenantId, string $branchId, int $turnoId, string $usuarioId, string $tipo, float $monto, string $motivo, ?string $comprobanteNro = null, ?string $observaciones = null): MovimientoCaja;
    public function getMovimientosByTurno(int $turnoId): array;
    public function recalcularTotalesTurno(int $turnoId): Turno;
}