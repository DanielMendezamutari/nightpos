<?php

declare(strict_types=1);

namespace App\Domain\Cash\Repositories;

use App\Domain\Cash\Entities\CashMovement;
use App\Domain\Cash\Entities\CashSession;
use App\Shared\Contracts\RepositoryInterface;

interface CashSessionRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId, bool $withMovements = true): ?CashSession;

    public function findOpenForUser(int $tenantId, int $branchId, int $userId): ?CashSession;

    /**
     * @return list<array{id: int, opened_by_user_id: int, status: string, official_shift_id: ?int}>
     */
    public function listOpenSessionsForBranch(int $tenantId, int $branchId): array;

    public function open(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        ?int $cashRegisterId,
        int $openedByUserId,
        string $openingAmount,
        ?string $openingNotes,
    ): CashSession;

    public function addMovement(
        int $tenantId,
        int $branchId,
        int $cashSessionId,
        string $movementType,
        string $amount,
        ?string $description,
        string $paymentMethod,
        int $createdByUserId,
        ?int $cashMovementReasonId = null,
        ?string $notes = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): CashMovement;

    public function close(
        int $sessionId,
        int $tenantId,
        int $closedByUserId,
        string $declaredClosingAmount,
        ?string $closingNotes,
    ): CashSession;

    /**
     * @param  array<string, mixed>  $closeBlockersSnapshot
     * @param  array<string, mixed>  $financialSummarySnapshot
     */
    public function forceClose(
        int $sessionId,
        int $tenantId,
        int $forcedClosedByUserId,
        string $expectedAmount,
        string $forcedCloseReason,
        string $forcedCloseNotes,
        array $closeBlockersSnapshot,
        array $financialSummarySnapshot,
    ): CashSession;

    public function sumMovements(int $cashSessionId): array;

    /**
     * @return array{income: string, expense: string}
     */
    public function sumManualMovements(int $cashSessionId): array;

    /**
     * @return array<string, array{income: string, expense: string}>
     */
    public function sumMovementsByMethod(int $cashSessionId): array;

    /**
     * @return list<\App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel>
     */
    public function listForAdmin(
        int $tenantId,
        ?int $branchId,
        ?string $status = null,
        ?int $officialShiftId = null,
        ?int $cashierUserId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array;

    public function findModelForAdmin(int $id, int $tenantId): ?\App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
}
