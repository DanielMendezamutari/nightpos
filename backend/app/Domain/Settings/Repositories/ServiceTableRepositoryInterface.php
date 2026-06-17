<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface ServiceTableRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(
        int $tenantId,
        int $branchId,
        bool $activeOnly = false,
        ?int $serviceAreaId = null,
    ): array;

    public function findById(int $id, int $tenantId, int $branchId): ?array;

    public function create(
        int $tenantId,
        int $branchId,
        int $serviceAreaId,
        string $code,
        string $label,
        int $sortOrder,
        string $status,
    ): array;

    public function update(
        int $id,
        int $tenantId,
        int $branchId,
        string $label,
        int $sortOrder,
        string $status,
    ): array;

    public function codeExists(int $branchId, string $code, ?int $exceptId = null): bool;
}
