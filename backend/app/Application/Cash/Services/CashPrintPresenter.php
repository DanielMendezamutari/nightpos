<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Application\Cash\Support\AdminCashSessionMapper;
use App\Application\Cash\Support\CashMapper;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Cash\Entities\CashMovement;
use App\Domain\Cash\Entities\CashSession;
use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Entities\ShiftClosure;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class CashPrintPresenter
{
    /**
     * @return array<string, mixed>|null
     */
    public static function movement(int $movementId, int $tenantId): ?array
    {
        $model = CashMovementModel::query()
            ->with(['reason', 'session'])
            ->where('id', $movementId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($model === null) {
            return null;
        }

        $cashierName = (string) (UserModel::query()->whereKey($model->created_by_user_id)->value('name') ?? '');
        $branchName = (string) (BranchModel::query()->whereKey($model->branch_id)->value('name') ?? '');

        return [
            'movement' => CashMapper::movement(self::mapMovementEntity($model)),
            'cashier_name' => $cashierName,
            'branch_name' => $branchName !== '' ? $branchName : null,
            'cash_session_id' => (int) $model->cash_session_id,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function cashClose(CashSession $session, array $financial, int $tenantId): ?array
    {
        $model = CashSessionModel::query()
            ->where('id', $session->id)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($model === null) {
            return null;
        }

        $cashierName = (string) (UserModel::query()->whereKey($session->closedByUserId ?? $session->openedByUserId)->value('name') ?? '');
        $branchName = (string) (BranchModel::query()->whereKey($session->branchId)->value('name') ?? '');

        $payload = [
            'session' => CashMapper::session($session),
            'summary' => $financial,
            'cashier_name' => $cashierName,
            'branch_name' => $branchName !== '' ? $branchName : null,
            'is_forced_close' => (bool) $model->is_forced_close,
        ];

        if ($model->is_forced_close) {
            $payload['forced_close'] = AdminCashSessionMapper::forceCloseMeta($model);
            $payload['blocker_messages'] = array_map(
                static fn (array $b) => (string) ($b['message'] ?? ''),
                $model->close_blockers_snapshot['blockers'] ?? [],
            );
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function shiftClose(OfficialShift $shift, ?ShiftClosure $closure, array $summary): ?array
    {
        if ($closure === null) {
            return null;
        }

        return [
            'shift' => ShiftMapper::shift($shift, $closure),
            'summary' => $summary,
            'branch_name' => $shift->branchName,
            'closed_by_name' => $shift->closedByName,
        ];
    }

    private static function mapMovementEntity(CashMovementModel $model): CashMovement
    {
        return new CashMovement(
            id: (int) $model->id,
            cashSessionId: (int) $model->cash_session_id,
            movementType: (string) $model->movement_type,
            amount: (string) $model->amount,
            description: $model->description,
            paymentMethod: (string) $model->payment_method,
            createdByUserId: (int) $model->created_by_user_id,
            createdAt: $model->created_at?->format('Y-m-d H:i:s') ?? '',
            cashMovementReasonId: $model->cash_movement_reason_id !== null ? (int) $model->cash_movement_reason_id : null,
            notes: $model->notes,
            reasonName: $model->reason?->name,
        );
    }
}
