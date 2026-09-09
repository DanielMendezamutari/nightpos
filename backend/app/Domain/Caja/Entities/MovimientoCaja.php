<?php

declare(strict_types=1);

namespace App\Domain\Caja\Entities;

final class MovimientoCaja
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private readonly string $branchId,
        private readonly int $turnoId,
        private readonly string $usuarioId,
        private readonly string $tipo,
        private readonly float $monto,
        private readonly string $motivo,
        private readonly ?string $comprobanteNro = null,
        private readonly ?string $observaciones = null,
        private readonly ?string $createdAt = null,
        private readonly ?string $usuarioNombre = null,
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function branchId(): string { return $this->branchId; }
    public function turnoId(): int { return $this->turnoId; }
    public function usuarioId(): string { return $this->usuarioId; }
    public function tipo(): string { return $this->tipo; }
    public function monto(): float { return $this->monto; }
    public function motivo(): string { return $this->motivo; }
    public function comprobanteNro(): ?string { return $this->comprobanteNro; }
    public function observaciones(): ?string { return $this->observaciones; }
    public function createdAt(): ?string { return $this->createdAt; }
    public function usuarioNombre(): ?string { return $this->usuarioNombre; }
}