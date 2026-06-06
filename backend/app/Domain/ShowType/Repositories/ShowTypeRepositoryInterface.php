<?php

declare(strict_types=1);

namespace App\Domain\ShowType\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface ShowTypeRepositoryInterface extends RepositoryInterface
{
    /** @return list<array<string, mixed>> */
    public function listForBranch(int $tenantId, ?int $branchId): array;

    public function nameExists(int $tenantId, string $name, ?int $exceptId = null): bool;

    public function create(
        int $tenantId,
        ?int $branchId,
        string $name,
        ?string $suggestedPrice,
        string $status,
    ): array;

    public function update(
        int $id,
        int $tenantId,
        string $name,
        ?string $suggestedPrice,
        string $status,
    ): array;

    public function findById(int $id, int $tenantId): ?array;
}
