<?php

declare(strict_types=1);

namespace App\Domain\Caja\Entities;

final class Turno
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private readonly string $branchId,
        private readonly string $cajeroId,
        private readonly string $fechaInicio,
        private ?string $fechaFin = null,
        private float $montoInicialBs = 0.0,
        private float $montoInicialUsd = 0.0,
        private ?float $montoFinalBs = null,
        private ?float $montoFinalUsd = null,
        private float $totalVentasEfectivo = 0.0,
        private float $totalVentasQr = 0.0,
        private float $totalVentasTarjeta = 0.0,
        private float $totalGastos = 0.0,
        private float $diferencia = 0.0,
        private string $estado = 'ABIERTO',
        private ?string $observaciones = null,
        private ?string $cajeroNombre = null,
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function branchId(): string { return $this->branchId; }
    public function cajeroId(): string { return $this->cajeroId; }
    public function fechaInicio(): string { return $this->fechaInicio; }
    public function fechaFin(): ?string { return $this->fechaFin; }
    public function montoInicialBs(): float { return $this->montoInicialBs; }
    public function montoInicialUsd(): float { return $this->montoInicialUsd; }
    public function montoFinalBs(): ?float { return $this->montoFinalBs; }
    public function montoFinalUsd(): ?float { return $this->montoFinalUsd; }
    public function totalVentasEfectivo(): float { return $this->totalVentasEfectivo; }
    public function totalVentasQr(): float { return $this->totalVentasQr; }
    public function totalVentasTarjeta(): float { return $this->totalVentasTarjeta; }
    public function totalGastos(): float { return $this->totalGastos; }
    public function diferencia(): float { return $this->diferencia; }
    public function estado(): string { return $this->estado; }
    public function observaciones(): ?string { return $this->observaciones; }
    public function cajeroNombre(): ?string { return $this->cajeroNombre; }

    public function totalVentas(): float
    {
        return round($this->totalVentasEfectivo + $this->totalVentasQr + $this->totalVentasTarjeta, 2);
    }

    public function efectivoEsperado(): float
    {
        return round($this->montoInicialBs + $this->totalVentasEfectivo - $this->totalGastos, 2);
    }
}