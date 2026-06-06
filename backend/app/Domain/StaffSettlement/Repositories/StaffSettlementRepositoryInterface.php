<?php

declare(strict_types=1);

namespace App\Domain\StaffSettlement\Repositories;

interface StaffSettlementRepositoryInterface
{
    /**
     * @return array{
     *   created_items: int,
     *   settlements_touched: int,
     *   shift_id: int
     * }
     */
    public function generateForShift(int $tenantId, int $branchId, int $officialShiftId): array;

    /**
     * @return array<string, mixed>
     */
    public function getCurrentShiftOverview(int $tenantId, int $branchId, int $officialShiftId, ?int $onlyStaffUserId): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $tenantId, int $branchId, ?int $onlyStaffUserId): ?array;

    public function markPaid(int $id, int $tenantId, int $branchId, int $paidByUserId, ?string $notes): array;

    /**
     * @param  array{
     *   official_shift_id?: int|null,
     *   staff_user_id?: int|null,
     *   settlement_type?: string|null,
     *   status?: string|null,
     *   date_from?: string|null,
     *   date_to?: string|null,
     * }  $filters
     * @return list<array<string, mixed>>
     */
    public function listHistory(int $tenantId, int $branchId, ?int $onlyStaffUserId, array $filters, int $limit): array;

    public function saleItemAlreadySettled(int $saleItemId, string $sourceType): bool;

    public function sourceAlreadySettled(int $sourceId, string $sourceType): bool;

    public function resolveOverviewShiftId(int $tenantId, int $branchId): ?int;
}
