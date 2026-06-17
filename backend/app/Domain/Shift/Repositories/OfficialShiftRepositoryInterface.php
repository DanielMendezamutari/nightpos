<?php

declare(strict_types=1);

namespace App\Domain\Shift\Repositories;

use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Entities\ShiftClosure;
use App\Shared\Contracts\RepositoryInterface;

interface OfficialShiftRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId): ?OfficialShift;

    public function findOpenForBranch(int $tenantId, int $branchId): ?OfficialShift;

    /**
     * @return list<OfficialShift>
     */
    public function listForBranch(int $tenantId, int $branchId, int $limit = 50): array;

    public function open(
        int $tenantId,
        int $branchId,
        string $name,
        string $shiftType,
        string $businessDate,
        string $startsAt,
        string $endsAt,
        int $openedByUserId,
        ?string $notes,
    ): OfficialShift;

    public function close(int $shiftId, int $tenantId, int $closedByUserId): OfficialShift;

    /**
     * Cierra un turno AUTO vencido por rotación de horario (status CLOSED + nota).
     */
    public function markAutoClosed(int $shiftId, int $tenantId, int $closedByUserId): OfficialShift;

    /**
     * @return array{
     *   total_cash: string,
     *   total_qr: string,
     *   total_card: string,
     *   total_sales: string,
     *   total_manual_income: string,
     *   total_manual_expense: string,
     *   expected_cash: string,
     * }
     */
    public function buildSummaryTotals(int $officialShiftId, int $tenantId, int $branchId): array;

    public function createClosure(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        array $totals,
        string $countedCash,
        string $cashDifference,
        int $closedByUserId,
        ?string $notes,
    ): ShiftClosure;

    public function findClosureByShiftId(int $officialShiftId, int $tenantId): ?ShiftClosure;

    public function hasOpenCashSessions(int $officialShiftId): bool;
}
