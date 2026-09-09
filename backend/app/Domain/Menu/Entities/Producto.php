<?php

declare(strict_types=1);

namespace App\Domain\Menu\Entities;

final class Producto
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private readonly int $categoriaId,
        private string $nombre,
        private ?string $codigo = null,
        private ?string $descripcion = null,
        private float $precio = 0.0,
        private float $costo = 0.0,
        private int $tiempoPreparacion = 15,
        private string $destinoImpresion = 'COCINA',
        private ?string $imagen = null,
        private bool $activo = true,
        private int $orden = 1,
        private ?string $categoriaNombre = null,
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function categoriaId(): int { return $this->categoriaId; }
    public function nombre(): string { return $this->nombre; }
    public function codigo(): ?string { return $this->codigo; }
    public function descripcion(): ?string { return $this->descripcion; }
    public function precio(): float { return $this->precio; }
    public function costo(): float { return $this->costo; }
    public function tiempoPreparacion(): int { return $this->tiempoPreparacion; }
    public function destinoImpresion(): string { return $this->destinoImpresion; }
    public function imagen(): ?string { return $this->imagen; }
    public function activo(): bool { return $this->activo; }
    public function orden(): int { return $this->orden; }
    public function categoriaNombre(): ?string { return $this->categoriaNombre; }
}