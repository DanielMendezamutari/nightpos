<?php

declare(strict_types=1);

namespace App\Domain\Salon\Entities;

final class Salon
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private readonly string $branchId,
        private string $codigo,
        private string $nombre,
        private ?string $impresoraCuenta = null,
        private ?string $impresoraFactura = null,
        private int $orden = 1,
        private bool $activo = true,
        private int $totalMesas = 0,
        private int $mesasOcupadas = 0,
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function branchId(): string { return $this->branchId; }
    public function codigo(): string { return $this->codigo; }
    public function nombre(): string { return $this->nombre; }
    public function impresoraCuenta(): ?string { return $this->impresoraCuenta; }
    public function impresoraFactura(): ?string { return $this->impresoraFactura; }
    public function orden(): int { return $this->orden; }
    public function activo(): bool { return $this->activo; }
    public function totalMesas(): int { return $this->totalMesas; }
    public function mesasOcupadas(): int { return $this->mesasOcupadas; }

    public function porcentajeOcupacion(): float
    {
        if ($this->totalMesas === 0) return 0.0;
        return round(($this->mesasOcupadas / $this->totalMesas) * 100, 1);
    }
}