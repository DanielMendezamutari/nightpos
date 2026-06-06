<?php

declare(strict_types=1);

namespace App\Application\Shift\Support;

use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Entities\ShiftClosure;
use App\Domain\Shift\ValueObjects\ShiftType;

final class ShiftMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function shift(OfficialShift $shift, ?ShiftClosure $closure = null): array
    {
        $type = ShiftType::fromString($shift->shiftType);

        $data = [
            'id' => $shift->id,
            'tenant_id' => $shift->tenantId,
            'branch_id' => $shift->branchId,
            'branch_name' => $shift->branchName,
            'name' => $shift->name,
            'shift_type' => $shift->shiftType,
            'shift_type_label' => $type->label(),
            'business_date' => $shift->businessDate,
            'starts_at' => $shift->startsAt,
            'ends_at' => $shift->endsAt,
            'status' => $shift->status,
            'opened_by_user_id' => $shift->openedByUserId,
            'opened_by_name' => $shift->openedByName,
            'closed_by_user_id' => $shift->closedByUserId,
            'closed_by_name' => $shift->closedByName,
            'opened_at' => $shift->openedAt,
            'closed_at' => $shift->closedAt,
            'notes' => $shift->notes,
            'auto_created' => $shift->notes !== null && str_contains($shift->notes, 'automáticamente'),
        ];

        if ($closure !== null) {
            $data['closure'] = self::closure($closure);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function closure(ShiftClosure $closure): array
    {
        return [
            'id' => $closure->id,
            'official_shift_id' => $closure->officialShiftId,
            'total_cash' => $closure->totalCash,
            'total_qr' => $closure->totalQr,
            'total_card' => $closure->totalCard,
            'total_sales' => $closure->totalSales,
            'total_manual_income' => $closure->totalManualIncome,
            'total_manual_expense' => $closure->totalManualExpense,
            'total_girl_payouts' => $closure->totalGirlPayouts,
            'total_waiter_payouts' => $closure->totalWaiterPayouts,
            'expected_cash' => $closure->expectedCash,
            'counted_cash' => $closure->countedCash,
            'cash_difference' => $closure->cashDifference,
            'status' => $closure->status,
            'closed_by_user_id' => $closure->closedByUserId,
            'closed_at' => $closure->closedAt,
            'notes' => $closure->notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function shiftListItem(OfficialShift $shift): array
    {
        $closure = null;
        $type = ShiftType::fromString($shift->shiftType);

        return [
            'id' => $shift->id,
            'business_date' => $shift->businessDate,
            'shift_type' => $shift->shiftType,
            'shift_type_label' => $type->label(),
            'name' => $shift->name,
            'status' => $shift->status,
            'opened_by_name' => $shift->openedByName,
            'closed_by_name' => $shift->closedByName,
            'total_sales' => null,
            'cash_difference' => null,
        ];
    }
}
