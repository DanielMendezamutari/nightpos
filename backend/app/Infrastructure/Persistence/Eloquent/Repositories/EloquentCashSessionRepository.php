<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Cash\Entities\CashMovement;
use App\Domain\Cash\Entities\CashSession;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashMovementType;
use App\Domain\Cash\ValueObjects\CashSessionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use Illuminate\Support\Carbon;

final class EloquentCashSessionRepository implements CashSessionRepositoryInterface
{
    public function findById(int $id, int $tenantId, bool $withMovements = true): ?CashSession
    {
        $query = CashSessionModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId);

        if ($withMovements) {
            $query->with(['movements.reason']);
        }

        $model = $query->first();

        return $model ? $this->mapSession($model, $withMovements) : null;
    }

    public function findOpenForUser(int $tenantId, int $branchId, int $userId): ?CashSession
    {
        $model = CashSessionModel::query()
            ->with(['movements.reason'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('opened_by_user_id', $userId)
            ->where('status', CashSessionStatus::OPEN)
            ->first();

        return $model ? $this->mapSession($model) : null;
    }

    public function listOpenSessionsForBranch(int $tenantId, int $branchId): array
    {
        return CashSessionModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', CashSessionStatus::OPEN)
            ->orderBy('id')
            ->get(['id', 'opened_by_user_id', 'status', 'official_shift_id'])
            ->map(static fn (CashSessionModel $model) => [
                'id' => (int) $model->id,
                'opened_by_user_id' => (int) $model->opened_by_user_id,
                'status' => (string) $model->status,
                'official_shift_id' => $model->official_shift_id !== null ? (int) $model->official_shift_id : null,
            ])
            ->all();
    }

    public function open(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        ?int $cashRegisterId,
        int $openedByUserId,
        string $openingAmount,
        ?string $openingNotes,
    ): CashSession {
        $model = CashSessionModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_register_id' => $cashRegisterId,
            'opened_by_user_id' => $openedByUserId,
            'status' => CashSessionStatus::OPEN,
            'opening_amount' => $openingAmount,
            'opening_notes' => $openingNotes,
            'opened_at' => Carbon::now(),
        ]);

        return $this->mapSession($model->fresh(), false);
    }

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
    ): CashMovement {
        $model = CashMovementModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'cash_session_id' => $cashSessionId,
            'movement_type' => $movementType,
            'amount' => $amount,
            'description' => $description,
            'payment_method' => $paymentMethod,
            'cash_movement_reason_id' => $cashMovementReasonId,
            'notes' => $notes,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'created_by_user_id' => $createdByUserId,
            'created_at' => Carbon::now(),
        ]);

        return $this->mapMovement($model->fresh(['reason']));
    }

    public function close(
        int $sessionId,
        int $tenantId,
        int $closedByUserId,
        string $declaredClosingAmount,
        ?string $closingNotes,
    ): CashSession {
        $model = CashSessionModel::query()
            ->where('id', $sessionId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($model === null) {
            throw new CashSessionNotFoundException();
        }

        $byMethod = $this->sumMovementsByMethod($sessionId);
        $cash = $byMethod['CASH'];
        $expected = (float) $model->opening_amount
            + (float) $cash['income']
            - (float) $cash['expense'];
        $declared = (float) $declaredClosingAmount;
        $difference = round($declared - $expected, 2);

        $model->update([
            'status' => CashSessionStatus::CLOSED,
            'closed_by_user_id' => $closedByUserId,
            'expected_amount' => $expected,
            'declared_closing_amount' => $declaredClosingAmount,
            'difference_amount' => $difference,
            'closing_notes' => $closingNotes,
            'closed_at' => Carbon::now(),
        ]);

        return $this->mapSession($model->fresh()->load(['movements.reason']));
    }

    public function sumMovements(int $cashSessionId): array
    {
        $income = (float) CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId)
            ->where('movement_type', CashMovementType::INCOME)
            ->sum('amount');

        $expense = (float) CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId)
            ->where('movement_type', CashMovementType::EXPENSE)
            ->sum('amount');

        return [
            'income' => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
        ];
    }

    public function sumManualMovements(int $cashSessionId): array
    {
        $income = (float) CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId)
            ->where('movement_type', CashMovementType::INCOME)
            ->where('description', 'not like', 'Cobro comanda%')
            ->where('description', 'not like', 'Venta directa%')
            ->sum('amount');

        $expense = (float) CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId)
            ->where('movement_type', CashMovementType::EXPENSE)
            ->sum('amount');

        return [
            'income' => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
        ];
    }

    public function sumMovementsByMethod(int $cashSessionId): array
    {
        $methods = ['CASH', 'QR', 'CARD', 'OTHER'];
        $result = [];

        foreach ($methods as $method) {
            $income = (float) CashMovementModel::query()
                ->where('cash_session_id', $cashSessionId)
                ->where('movement_type', CashMovementType::INCOME)
                ->where('payment_method', $method)
                ->sum('amount');

            $expense = (float) CashMovementModel::query()
                ->where('cash_session_id', $cashSessionId)
                ->where('movement_type', CashMovementType::EXPENSE)
                ->where('payment_method', $method)
                ->sum('amount');

            $result[$method] = [
                'income' => number_format($income, 2, '.', ''),
                'expense' => number_format($expense, 2, '.', ''),
            ];
        }

        return $result;
    }

    public function listForAdmin(
        int $tenantId,
        ?int $branchId,
        ?string $status = null,
        ?int $officialShiftId = null,
        ?int $cashierUserId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $query = CashSessionModel::query()
            ->with(['opener', 'branch', 'tenant', 'officialShift', 'closer'])
            ->where('tenant_id', $tenantId);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($officialShiftId !== null && $officialShiftId > 0) {
            $query->where('official_shift_id', $officialShiftId);
        }

        if ($cashierUserId !== null && $cashierUserId > 0) {
            $query->where('opened_by_user_id', $cashierUserId);
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $query->whereDate('opened_at', '>=', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $query->whereDate('opened_at', '<=', $dateTo);
        }

        return $query
            ->orderByDesc('opened_at')
            ->get()
            ->all();
    }

    public function findModelForAdmin(int $id, int $tenantId): ?CashSessionModel
    {
        return CashSessionModel::query()
            ->with(['opener', 'closer', 'branch', 'tenant', 'officialShift', 'movements.reason'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    private function mapSession(CashSessionModel $model, bool $withMovements = true): CashSession
    {
        $totals = $this->sumMovements((int) $model->id);
        $movements = [];

        if ($withMovements && $model->relationLoaded('movements')) {
            $movements = $model->movements
                ->sortByDesc('id')
                ->map(fn (CashMovementModel $m) => $this->mapMovement($m))
                ->values()
                ->all();
        }

        return new CashSession(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            officialShiftId: $model->official_shift_id !== null ? (int) $model->official_shift_id : null,
            cashRegisterId: $model->cash_register_id !== null ? (int) $model->cash_register_id : null,
            openedByUserId: (int) $model->opened_by_user_id,
            closedByUserId: $model->closed_by_user_id !== null ? (int) $model->closed_by_user_id : null,
            status: $model->status,
            openingAmount: (string) $model->opening_amount,
            expectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
            declaredClosingAmount: $model->declared_closing_amount !== null ? (string) $model->declared_closing_amount : null,
            differenceAmount: $model->difference_amount !== null ? (string) $model->difference_amount : null,
            openingNotes: $model->opening_notes,
            closingNotes: $model->closing_notes,
            openedAt: $model->opened_at?->toIso8601String() ?? '',
            closedAt: $model->closed_at?->toIso8601String(),
            incomeTotal: $totals['income'],
            expenseTotal: $totals['expense'],
            movements: $movements,
        );
    }

    private function mapMovement(CashMovementModel $model): CashMovement
    {
        $reasonName = $model->relationLoaded('reason') && $model->reason !== null
            ? $model->reason->name
            : null;

        return new CashMovement(
            id: (int) $model->id,
            cashSessionId: (int) $model->cash_session_id,
            movementType: $model->movement_type,
            amount: (string) $model->amount,
            description: $model->description,
            paymentMethod: $model->payment_method,
            createdByUserId: (int) $model->created_by_user_id,
            createdAt: $model->created_at?->toIso8601String() ?? '',
            cashMovementReasonId: $model->cash_movement_reason_id !== null ? (int) $model->cash_movement_reason_id : null,
            notes: $model->notes,
            reasonName: $reasonName,
        );
    }
}
