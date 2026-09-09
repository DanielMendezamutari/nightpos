<?php

declare(strict_types=1);

namespace App\Domain\Mesa\Repositories;

use App\Domain\Mesa\Entities\Mesa;

interface MesaRepositoryInterface
{
    /**
     * @return Mesa[]
     */
    public function getMesasBySalon(int $salonId): array;

    public function findById(int $id): ?Mesa;

    public function abrirMesa(int $mesaId, string $meseroId, int $personas, ?string $clienteNombre, ?string $notas): int;

    public function cambiarMesa(int $mesaOrigenId, int $mesaDestinoId): bool;

    public function solicitarPrecuenta(int $mesaId): bool;

    public function liberarMesa(int $mesaId): bool;
}