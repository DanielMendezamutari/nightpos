<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Repositories;

interface RoomServiceRepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $tenantId): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listForShift(int $tenantId, int $branchId, int $officialShiftId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(int $tenantId, int $branchId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listDue(int $tenantId, int $branchId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listFinishedToday(int $tenantId, int $branchId): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findDueUnalerted(): array;

    /**
     * @return array{total_amount: float, count: int}
     */
    public function summarizeForShift(int $tenantId, int $branchId, int $officialShiftId): array;

    /**
     * @return array<string, mixed>
     */
    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $girlUserId,
        ?int $roomId,
        ?string $roomNumber,
        ?string $roomLabel,
        string $unitPrice,
        string $totalAmount,
        string $girlPercent,
        string $grossGirlAmount,
        string $girlAmount,
        string $houseAmount,
        string $cleaningAmount,
        int $registeredByUserId,
        string $registeredAt,
        string $startedAt,
        int $durationMinutes,
        string $expectedEndsAt,
        ?string $notes,
        int $cashSessionId,
        string $paymentMethod,
    ): array;

    public function attachCashMovement(int $id, int $tenantId, int $cashMovementId): void;

    public function markAlertSent(int $roomServiceId): void;

    public function markDue(int $roomServiceId): void;

    /**
     * @return array<string, mixed>|null
     */
    public function finish(int $id, int $tenantId, int $branchId, ?int $cleaningUserId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function check(int $id, int $tenantId, int $branchId, int $checkedByUserId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function cancel(int $id, int $tenantId, int $branchId): ?array;
}
