<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;

final class SettlementPrintPresenter
{
    /**
     * @return array<string, mixed>|null
     */
    public function payment(
        int $settlementId,
        int $tenantId,
        bool $isReprint = false,
        ?int $reprintNumber = null,
        ?string $reprintedByName = null,
        ?string $reprintedAt = null,
    ): ?array {
        $settlement = StaffSettlementModel::query()
            ->with(['staffUser', 'paidBy', 'officialShift'])
            ->where('id', $settlementId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($settlement === null || $settlement->status !== 'PAID') {
            return null;
        }

        $branch = BranchModel::query()->find($settlement->branch_id);
        $adjustments = StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->orderBy('id')
            ->get();

        $cleaning = '0.00';
        $manualDiscount = '0.00';
        $manualDiscountReason = null;
        $fines = [];

        foreach ($adjustments as $row) {
            if ($row->adjustment_type === SettlementAdjustmentType::CleaningDeduction->value) {
                $cleaning = number_format((float) $row->amount, 2, '.', '');
            } elseif ($row->adjustment_type === SettlementAdjustmentType::ManualDiscount->value) {
                $manualDiscount = number_format((float) $row->amount, 2, '.', '');
                $manualDiscountReason = $row->notes;
            } elseif ($row->adjustment_type === SettlementAdjustmentType::ManualFine->value) {
                $fines[] = [
                    'reason' => $row->notes,
                    'amount' => number_format((float) $row->amount, 2, '.', ''),
                ];
            }
        }

        $cutNumber = $this->resolveCutNumber($settlement);
        $shift = $settlement->officialShift;
        $paidByName = $settlement->paidBy?->name;

        $cashMovement = $settlement->cash_movement_id !== null
            ? CashMovementModel::query()->find($settlement->cash_movement_id)
            : null;

        $items = StaffSettlementItemModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->get();

        $waiterSnapshot = SettlementWaiterSnapshotResolver::resolve(
            (string) $settlement->settlement_type,
            (string) $settlement->staff_role,
            $items,
        );

        return [
            'settlement' => [
                'id' => (int) $settlement->id,
                'ticket_number' => $settlement->ticket_number,
                'staff_name' => $settlement->staffUser?->name,
                'staff_role' => $settlement->staff_role,
                'settlement_type' => $settlement->settlement_type,
                'cut_number' => $cutNumber,
                'cut_label' => sprintf('Corte #%d', $cutNumber),
                'gross_amount' => number_format((float) $settlement->gross_amount, 2, '.', ''),
                'cleaning_amount' => $cleaning,
                'manual_discount_amount' => $manualDiscount,
                'manual_discount_reason' => $manualDiscountReason,
                'fines' => $fines,
                'net_amount' => number_format((float) $settlement->net_amount, 2, '.', ''),
                'payment_method' => $settlement->payment_method,
                'paid_by_name' => $paidByName,
                'paid_at' => $settlement->paid_at?->format('Y-m-d H:i:s'),
                'cash_session_id' => $settlement->cash_session_id,
                'cash_movement_id' => $settlement->cash_movement_id,
                'notes' => $settlement->notes,
                'waiter_snapshot' => $waiterSnapshot,
                'waiter_sales_total' => $waiterSnapshot['sales_total'] ?? null,
                'commission_percent' => $waiterSnapshot['commission_percent'] ?? null,
                'commission_amount' => $waiterSnapshot['commission_amount'] ?? null,
            ],
            'branch_name' => $branch?->name,
            'branch_code' => $branch?->code,
            'shift_name' => $shift?->name,
            'shift_business_date' => $shift?->business_date?->format('Y-m-d'),
            'shift_type_label' => $shift?->shift_type === 'DAY' ? 'Día' : 'Noche',
            'cash_movement' => $cashMovement ? [
                'id' => $cashMovement->id,
                'amount' => number_format((float) $cashMovement->amount, 2, '.', ''),
            ] : null,
            'is_reprint' => $isReprint,
            'reprint_number' => $reprintNumber,
            'reprinted_by_name' => $reprintedByName,
            'reprinted_at' => $reprintedAt,
        ];
    }

    private function resolveCutNumber(StaffSettlementModel $settlement): int
    {
        return (int) StaffSettlementModel::query()
            ->where('official_shift_id', $settlement->official_shift_id)
            ->where('staff_user_id', $settlement->staff_user_id)
            ->where('settlement_type', $settlement->settlement_type)
            ->where('id', '<=', $settlement->id)
            ->count();
    }
}
