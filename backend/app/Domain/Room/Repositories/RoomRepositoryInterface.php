<?php

declare(strict_types=1);

namespace App\Domain\Room\Repositories;

interface RoomRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $tenantId, int $branchId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(int $tenantId, int $branchId, ?string $status = null): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listAvailable(int $tenantId, int $branchId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listCleaningOverview(int $tenantId, int $branchId): array;

    /**
     * @return array{available: int, occupied: int, cleaning: int, maintenance: int, total: int}
     */
    public function statusSummary(int $tenantId, int $branchId): array;

    public function codeExists(int $tenantId, int $branchId, string $code, ?int $excludeId = null): bool;

    /**
     * @return array<string, mixed>
     */
    public function create(
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $roomType,
        ?int $defaultDurationMinutes,
        ?string $suggestedPrice,
        ?string $notes,
    ): array;

    /**
     * @return array<string, mixed>|null
     */
    public function update(
        int $id,
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $roomType,
        ?int $defaultDurationMinutes,
        ?string $suggestedPrice,
        ?string $notes,
    ): ?array;

    public function occupyIfAvailable(int $roomId, int $tenantId, int $branchId): bool;

    public function setCleaning(int $roomId, int $tenantId, int $branchId): bool;

    /**
     * Libera habitación tras terminar pieza (OCCUPIED → AVAILABLE).
     */
    public function releaseAfterService(int $roomId, int $tenantId, int $branchId): bool;

    /**
     * @return array<string, mixed>|null
     */
    public function markClean(int $roomId, int $tenantId, int $branchId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function markMaintenance(int $roomId, int $tenantId, int $branchId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function markAvailable(int $roomId, int $tenantId, int $branchId): ?array;
}
