<?php

declare(strict_types=1);

namespace App\Domain\Mesa\Entities;

use App\Domain\Mesa\Enums\MesaEstado;
use DateTimeImmutable;

final class Mesa
{
    public function __construct(
        private readonly int $id,
        private readonly int $salonId,
        private string $codigo,
        private string $nombre,
        private int $capacidad = 4,
        private int $posicionX = 0,
        private int $posicionY = 0,
        private int $ancho = 100,
        private int $alto = 100,
        private string $forma = 'cuadrada',
        private bool $activo = true,
        private MesaEstado $estado = MesaEstado::LIBRE,
        private ?int $visitaId = null,
        private ?string $meseroNombre = null,
        private ?string $clienteNombre = null,
        private int $personas = 0,
        private float $totalConsumo = 0.0,
        private ?DateTimeImmutable $fechaApertura = null,
        private bool $imprimioCuenta = false,
    ) {}

    public function id(): int { return $this->id; }
    public function salonId(): int { return $this->salonId; }
    public function codigo(): string { return $this->codigo; }
    public function nombre(): string { return $this->nombre; }
    public function capacidad(): int { return $this->capacidad; }
    public function posicionX(): int { return $this->posicionX; }
    public function posicionY(): int { return $this->posicionY; }
    public function ancho(): int { return $this->ancho; }
    public function alto(): int { return $this->alto; }
    public function forma(): string { return $this->forma; }
    public function activo(): bool { return $this->activo; }
    public function estado(): MesaEstado { return $this->estado; }
    public function visitaId(): ?int { return $this->visitaId; }
    public function meseroNombre(): ?string { return $this->meseroNombre; }
    public function clienteNombre(): ?string { return $this->clienteNombre; }
    public function personas(): int { return $this->personas; }
    public function totalConsumo(): float { return $this->totalConsumo; }
    public function fechaApertura(): ?DateTimeImmutable { return $this->fechaApertura; }
    public function imprimioCuenta(): bool { return $this->imprimioCuenta; }

    public function minutosAbierta(): int
    {
        if ($this->fechaApertura === null) return 0;
        $ahora = new DateTimeImmutable();
        return (int) round(($ahora->getTimestamp() - $this->fechaApertura->getTimestamp()) / 60);
    }
}