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
        // Automatic girl cleaning deductions are disabled.
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

    public function upsertManualCleaningDeduction(
        StaffSettlementModel $settlement,
        float $grossAmount,
        float $cleaningAmount,
        int $userId,
        ?string $notes = null,
    ): StaffSettlementAdjustmentModel {
        $amount = number_format(-1 * abs($cleaningAmount), 2, '.', '');

        $adjustment = StaffSettlementAdjustmentModel::query()->updateOrCreate(
            [
                'staff_settlement_id' => $settlement->id,
                'adjustment_type' => SettlementAdjustmentType::CleaningDeduction->value,
            ],
            [
                'tenant_id' => $settlement->tenant_id,
                'branch_id' => $settlement->branch_id,
                'amount' => $amount,
                'calculation_base' => number_format($grossAmount, 2, '.', ''),
                'notes' => $notes,
                'dedup_key' => sprintf('manual_cleaning:%d', (int) $settlement->id),
                'discount_mode' => null,
                'discount_value' => null,
                'created_by_user_id' => $userId,
            ],
        );

        return $adjustment->fresh() ?? $adjustment;
    }

    public function removeCleaningDeduction(int $settlementId): void
    {
        StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->delete();
    }
}
