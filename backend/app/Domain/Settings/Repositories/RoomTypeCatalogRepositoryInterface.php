<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface RoomTypeCatalogRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(int $tenantId, int $branchId, bool $activeOnly = false): array;

    public function findById(int $id, int $tenantId): ?array;

    public function findByCode(int $tenantId, string $code): ?array;

    public function create(
        int $tenantId,
        ?int $branchId,
        string $code,
        string $name,
        int $defaultDurationMinutes,
        string $suggestedPrice,
        string $status,
    ): array;

    public function update(
        int $id,
        int $tenantId,
        string $name,
        int $defaultDurationMinutes,
        string $suggestedPrice,
        string $status,
    ): array;

    public function codeExists(int $tenantId, string $code, ?int $exceptId = null): bool;
}
