<?php

declare(strict_types=1);

namespace App\Domain\Notification\Repositories;

interface NotificationRepositoryInterface
{
    public function existsForSource(string $type, string $sourceType, int $sourceId): bool;

    public function existsForSourceRole(string $type, string $sourceType, int $sourceId, string $roleTarget): bool;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
        int $limit,
    ): array;

    public function countUnreadForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
    ): int;

    public function markRead(int $id, int $tenantId, int $branchId): bool;

    public function markReadForRoomSource(int $tenantId, int $branchId, int $roomServiceId): void;

    public function markAllReadForScope(
        int $tenantId,
        int $branchId,
        ?int $userId,
        ?string $roleTarget,
        bool $managerView,
    ): int;
}
