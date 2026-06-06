<?php

declare(strict_types=1);

namespace App\Domain\SSE\Repositories;

interface SseTokenRepositoryInterface
{
    public function create(
        int $tenantId,
        int $branchId,
        int $userId,
        ?string $roleScope,
        int $ttlSeconds = 60
    ): string;

    /**
     * Find a valid (non-expired) token and return its context,
     * or null if missing / expired.
     *
     * @return array{tenant_id:int, branch_id:int, user_id:int, role_scope:string|null}|null
     */
    public function findValid(string $token): ?array;

    public function purgeExpired(): void;
}
