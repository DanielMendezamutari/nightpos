<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface ServiceAreaRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(int $tenantId, int $branchId, bool $activeOnly = false): array;

    public function findById(int $id, int $tenantId, int $branchId): ?array;

    public function create(
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $areaType,
        string $status,
    ): array;

    public function update(int $id, int $tenantId, string $name, string $areaType, string $status): array;

    public function codeExists(int $branchId, string $code, ?int $exceptId = null): bool;
}
