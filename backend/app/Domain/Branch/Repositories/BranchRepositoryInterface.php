<?php

declare(strict_types=1);

namespace App\Domain\Branch\Repositories;

use App\Domain\Branch\Entities\Branch;
use App\Shared\Contracts\RepositoryInterface;

interface BranchRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Branch;

    public function findByTenantAndCode(int $tenantId, string $code): ?Branch;

    /** @return list<Branch> */
    public function listByTenant(int $tenantId): array;

    /** @return list<Branch> */
    public function listAccessibleForUser(int $userId, int $tenantId): array;

    public function create(
        int $tenantId,
        string $name,
        string $code,
        ?string $address,
        string $status,
    ): Branch;

    public function update(
        int $id,
        string $name,
        string $code,
        ?string $address,
        string $status,
    ): Branch;

    public function codeExistsForTenant(int $tenantId, string $code, ?int $exceptBranchId = null): bool;
}
