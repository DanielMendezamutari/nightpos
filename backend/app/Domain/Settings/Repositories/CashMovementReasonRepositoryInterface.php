<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface CashMovementReasonRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(int $tenantId, int $branchId, ?string $type = null, bool $activeOnly = false): array;

    public function findById(int $id, int $tenantId): ?array;

    public function create(int $tenantId, ?int $branchId, string $type, string $name, string $status): array;

    public function update(int $id, int $tenantId, string $name, string $status): array;

    public function nameExists(int $tenantId, string $type, string $name, ?int $exceptId = null): bool;
}
