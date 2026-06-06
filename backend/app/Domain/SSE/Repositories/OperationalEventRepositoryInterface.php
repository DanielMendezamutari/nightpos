<?php

declare(strict_types=1);

namespace App\Domain\SSE\Repositories;

interface OperationalEventRepositoryInterface
{
    public function create(
        int $tenantId,
        int $branchId,
        string $type,
        array $payload,
        ?string $targetRole = null
    ): array;

    /**
     * Return events for a tenant/branch/roleScope since a given event ID.
     * If roleScope is null, return events where target_role IS NULL.
     * If roleScope is provided, return events where target_role IS NULL OR target_role = roleScope.
     *
     * @return array<int, array>
     */
    public function findSince(
        int $tenantId,
        int $branchId,
        ?string $roleScope,
        int $lastId,
        int $limit = 50
    ): array;
}
