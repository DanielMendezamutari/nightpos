<?php

declare(strict_types=1);

namespace App\Domain\Menu\Entities;

final class Categoria
{
    public function __construct(
        private readonly int $id,
        private readonly string $tenantId,
        private string $nombre,
        private ?string $codigo = null,
        private string $icono = 'ri-restaurant-line',
        private string $color = 'primary',
        private int $orden = 1,
        private bool $activo = true,
        private int $totalProductos = 0,
    ) {}

    public function id(): int { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function nombre(): string { return $this->nombre; }
    public function codigo(): ?string { return $this->codigo; }
    public function icono(): string { return $this->icono; }
    public function color(): string { return $this->color; }
    public function orden(): int { return $this->orden; }
    public function activo(): bool { return $this->activo; }
    public function totalProductos(): int { return $this->totalProductos; }
}