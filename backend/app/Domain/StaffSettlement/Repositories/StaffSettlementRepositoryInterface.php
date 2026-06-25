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
    public function generateForShift(int $tenantId, int $branchId, int $officialShiftId, ?int $scopeCashSessionId = null): array;

    /**
     * @return array<string, mixed>
     */
    public function getCurrentShiftOverview(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        ?int $onlyStaffUserId,
        ?int $cashSessionId = null,
    ): array;

    public function cashSessionHasActivity(int $tenantId, int $branchId, int $officialShiftId, int $cashSessionId): bool;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id, int $tenantId, int $branchId, ?int $onlyStaffUserId): ?array;

    public function markPaid(
        int $id,
        int $tenantId,
        int $branchId,
        int $paidByUserId,
        ?string $notes,
        string $paymentMethod,
        int $cashMovementId,
        string $ticketNumber,
    ): array;

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

    public function resolveOpenShiftId(int $tenantId, int $branchId): ?int;

    /**
     * @return array{
     *     sales: int,
     *     bracelets: int,
     *     rooms: int,
     *     shows: int,
     *     cleaning_tasks: int
     * }
     */
    public function countShiftSources(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): array;

    public function countUnsettledShiftSources(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): int;

    public function countPendingSettlements(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null, ?string $staffRole = null): int;

    public function sumPendingSettlementAmount(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): float;

    public function countGeneratedSettlements(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): int;

    /**
     * @return array{
     *     generated_pending_count: int,
     *     generated_pending_amount: string,
     *     unsettled_sources_count: int,
     *     already_generated_count: int,
     *     already_generated_pending_count: int
     * }
     */
    public function settlementScopeSummary(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): array;
}
