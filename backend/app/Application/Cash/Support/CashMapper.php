<?php

declare(strict_types=1);

namespace App\Application\Cash\Support;

use App\Domain\Cash\Entities\CashMovement;
use App\Domain\Cash\Entities\CashSession;

final class CashMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function session(CashSession $session): array
    {
        $data = [
            'id' => $session->id,
            'tenant_id' => $session->tenantId,
            'branch_id' => $session->branchId,
            'official_shift_id' => $session->officialShiftId,
            'cash_register_id' => $session->cashRegisterId,
            'opened_by_user_id' => $session->openedByUserId,
            'closed_by_user_id' => $session->closedByUserId,
            'status' => $session->status,
            'opening_amount' => $session->openingAmount,
            'expected_amount' => $session->expectedAmount,
            'declared_closing_amount' => $session->declaredClosingAmount,
            'difference_amount' => $session->differenceAmount,
            'opening_notes' => $session->openingNotes,
            'closing_notes' => $session->closingNotes,
            'opened_at' => $session->openedAt,
            'closed_at' => $session->closedAt,
            'income_total' => $session->incomeTotal,
            'expense_total' => $session->expenseTotal,
        ];

        if ($session->movements !== []) {
            $data['movements'] = array_map(static fn (CashMovement $m) => self::movement($m), $session->movements);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function movement(CashMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'cash_session_id' => $movement->cashSessionId,
            'movement_type' => $movement->movementType,
            'amount' => $movement->amount,
            'description' => $movement->description,
            'cash_movement_reason_id' => $movement->cashMovementReasonId,
            'reason_name' => $movement->reasonName,
            'notes' => $movement->notes,
            'payment_method' => $movement->paymentMethod,
            'created_by_user_id' => $movement->createdByUserId,
            'created_at' => $movement->createdAt,
        ];
    }
}
