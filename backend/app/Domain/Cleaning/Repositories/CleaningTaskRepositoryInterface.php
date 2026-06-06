<?php

declare(strict_types=1);

namespace App\Domain\Cleaning\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface CleaningTaskRepositoryInterface extends RepositoryInterface
{
    public function existsForRoomService(int $roomServiceId): bool;

    /**
     * @return array<string, mixed>|null
     */
    public function createIfNotExists(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $roomId,
        int $roomServiceId,
        int $cleaningUserId,
        string $amount,
    ): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForShiftAndUser(int $tenantId, int $branchId, int $officialShiftId, int $cleaningUserId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array;
}
