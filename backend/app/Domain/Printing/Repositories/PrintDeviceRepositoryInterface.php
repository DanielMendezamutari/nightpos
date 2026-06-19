<?php

declare(strict_types=1);

namespace App\Domain\Printing\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface PrintDeviceRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function create(
        int $tenantId,
        int $branchId,
        string $name,
        string $deviceKeyHash,
        string $deviceKeyPrefix,
        int $paperWidthMm,
        bool $autoPrintOrder,
    ): array;

    public function findById(int $id, int $tenantId, int $branchId): ?array;

    public function findByKeyPrefix(string $prefix): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listByBranch(int $tenantId, int $branchId): array;

    public function hasActiveDevice(int $tenantId, int $branchId): bool;

    /**
     * @param  array<string, mixed>  $fields
     */
    public function update(int $id, int $tenantId, int $branchId, array $fields): ?array;

    public function rotateKey(int $id, int $tenantId, int $branchId, string $hash, string $prefix): ?array;

    public function recordHeartbeat(
        int $id,
        int $tenantId,
        int $branchId,
        ?string $printerName,
        ?string $agentVersion,
        ?string $lastError,
    ): void;
}
