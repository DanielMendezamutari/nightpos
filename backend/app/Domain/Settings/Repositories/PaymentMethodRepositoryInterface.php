<?php

declare(strict_types=1);

namespace App\Domain\Settings\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface PaymentMethodRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForBranch(int $tenantId, int $branchId, bool $enabledOnly = false): array;

    public function findById(int $id, int $tenantId): ?array;

    public function findByCode(int $tenantId, int $branchId, string $code): ?array;

    public function create(
        int $tenantId,
        ?int $branchId,
        string $code,
        string $name,
        string $type,
        bool $enabled,
        bool $requiresReference,
    ): array;

    public function update(
        int $id,
        int $tenantId,
        string $name,
        bool $enabled,
        bool $requiresReference,
    ): array;

    public function codeExists(int $tenantId, string $code, ?int $exceptId = null): bool;

    public function hasEnabledCash(int $tenantId, int $branchId): bool;

    /**
     * @return list<string>
     */
    public function enabledLegacyCodes(int $tenantId, int $branchId): array;
}
