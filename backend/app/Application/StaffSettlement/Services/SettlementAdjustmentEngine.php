<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;

final class SettlementAdjustmentEngine
{
    public function syncCleaningDeduction(StaffSettlementModel $settlement, float $grossAmount): void
    {
        if ($settlement->status !== 'PENDING' || $settlement->settlement_type !== 'GIRL') {
            $this->removeCleaningDeduction((int) $settlement->id);

            return;
        }

        $threshold = (float) config('nightpos.girl_unique_cleaning.threshold', 100);
        $cleaningAmount = (float) config('nightpos.girl_unique_cleaning.amount', 10);
        $dedupKey = $this->cleaningDedupKey(
            (int) $settlement->official_shift_id,
            $settlement->cash_session_id !== null ? (int) $settlement->cash_session_id : null,
            (int) $settlement->staff_user_id,
        );

        if ($this->cleaningDeductionAlreadyApplied(
            (int) $settlement->tenant_id,
            $dedupKey,
            (int) $settlement->id,
        )) {
            $this->removeCleaningDeduction((int) $settlement->id);

            return;
        }

        if ($grossAmount + 0.009 < $threshold) {
            $this->removeCleaningDeduction((int) $settlement->id);

            return;
        }

        $amount = number_format(-1 * abs($cleaningAmount), 2, '.', '');

        StaffSettlementAdjustmentModel::query()->updateOrCreate(
            [
                'staff_settlement_id' => $settlement->id,
                'adjustment_type' => SettlementAdjustmentType::CleaningDeduction->value,
            ],
            [
                'tenant_id' => $settlement->tenant_id,
                'branch_id' => $settlement->branch_id,
                'amount' => $amount,
                'calculation_base' => number_format($grossAmount, 2, '.', ''),
                'notes' => sprintf('Limpieza única turno (≥ %.2f Bs)', $threshold),
                'dedup_key' => $dedupKey,
                'discount_mode' => null,
                'discount_value' => null,
                'created_by_user_id' => null,
            ],
        );
    }

    public function cleaningDedupKey(int $officialShiftId, ?int $cashSessionId, int $staffUserId): string
    {
        return sprintf(
            'cleaning:%d:%d:%d',
            $officialShiftId,
            $cashSessionId ?? 0,
            $staffUserId,
        );
    }

    public function cleaningDeductionAlreadyApplied(int $tenantId, string $dedupKey, int $exceptSettlementId): bool
    {
        return StaffSettlementAdjustmentModel::query()
            ->where('tenant_id', $tenantId)
            ->where('dedup_key', $dedupKey)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->where('staff_settlement_id', '!=', $exceptSettlementId)
            ->exists();
    }

    private function removeCleaningDeduction(int $settlementId): void
    {
        StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->delete();
    }
}
