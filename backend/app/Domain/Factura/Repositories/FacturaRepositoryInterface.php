<?php

declare(strict_types=1);

namespace App\Domain\Factura\Repositories;

use App\Domain\Factura\Entities\Factura;

interface FacturaRepositoryInterface
{
    public function emitirFactura(array $datos): Factura;
    public function getFacturas(string $tenantId, string $branchId, ?int $turnoId = null): array;
    public function findFacturaById(int $id): ?Factura;
    public function anularFactura(int $id, string $motivo): Factura;
    public function getSiguienteNroFactura(string $tenantId, string $branchId): int;
    public function getSiguienteNroRecibo(string $tenantId, string $branchId): int;
}