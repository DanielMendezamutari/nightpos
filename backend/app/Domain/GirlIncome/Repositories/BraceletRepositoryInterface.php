<?php

declare(strict_types=1);

namespace App\Domain\GirlIncome\Repositories;

interface BraceletRepositoryInterface
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
     * @return array{total_amount: float, quantity: int, count: int, average: float}
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
        ?int $waiterUserId,
        int $quantity,
        string $unitPrice,
        string $totalAmount,
        int $registeredByUserId,
        string $registeredAt,
        ?string $notes,
        int $cashSessionId,
        string $paymentMethod,
    ): array;

    public function attachCashMovement(int $id, int $tenantId, int $cashMovementId): void;
}
