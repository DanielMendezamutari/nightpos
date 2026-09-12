<?php

declare(strict_types=1);

namespace App\Domain\Factura\Entities;

final class Factura
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private readonly string $branchId,
        private readonly ?int $visitaId,
        private readonly int $turnoId,
        private readonly string $cajeroId,
        private readonly string $tipoComprobante,
        private readonly ?string $nroComprobante,
        private readonly int $nroFactura,
        private readonly string $cuf,
        private readonly ?string $cufd,
        private readonly string $tipoDocumento,
        private readonly string $numeroDocumento,
        private readonly string $razonSocial,
        private readonly ?string $correo,
        private readonly string $metodoPago,
        private readonly float $montoTotal,
        private readonly float $montoEfectivo,
        private readonly float $montoTarjeta = 0.0,
        private readonly float $montoQr = 0.0,
        private readonly ?string $segundoMetodoPago = null,
        private readonly ?string $nroTarjeta = null,
        private readonly float $montoCambio = 0.0,
        private readonly string $estado = 'VALIDA',
        private readonly ?string $codigoSiat = null,
        private readonly string $fechaEmision = '',
        private readonly ?string $cajeroNombre = null,
        private readonly ?string $mesaNumero = null,
        private readonly array $detalles = [],
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function branchId(): string { return $this->branchId; }
    public function visitaId(): ?int { return $this->visitaId; }
    public function turnoId(): int { return $this->turnoId; }
    public function cajeroId(): string { return $this->cajeroId; }
    public function nroFactura(): int { return $this->nroFactura; }
    public function tipoComprobante(): string { return $this->tipoComprobante; }
    public function nroComprobante(): ?string { return $this->nroComprobante; }
    public function cuf(): string { return $this->cuf; }
    public function cufd(): ?string { return $this->cufd; }
    public function tipoDocumento(): string { return $this->tipoDocumento; }
    public function numeroDocumento(): string { return $this->numeroDocumento; }
    public function razonSocial(): string { return $this->razonSocial; }
    public function correo(): ?string { return $this->correo; }
    public function metodoPago(): string { return $this->metodoPago; }
    public function montoTotal(): float { return $this->montoTotal; }
    public function montoEfectivo(): float { return $this->montoEfectivo; }
    public function montoTarjeta(): float { return $this->montoTarjeta; }
    public function montoQr(): float { return $this->montoQr; }
    public function segundoMetodoPago(): ?string { return $this->segundoMetodoPago; }
    public function nroTarjeta(): ?string { return $this->nroTarjeta; }
    public function montoCambio(): float { return $this->montoCambio; }
    public function estado(): string { return $this->estado; }
    public function codigoSiat(): ?string { return $this->codigoSiat; }
    public function fechaEmision(): string { return $this->fechaEmision; }
    public function cajeroNombre(): ?string { return $this->cajeroNombre; }
    public function mesaNumero(): ?string { return $this->mesaNumero; }
    public function detalles(): array { return $this->detalles; }
}
