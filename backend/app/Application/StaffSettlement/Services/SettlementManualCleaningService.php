<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;

final class SettlementManualCleaningService
{
    public function __construct(
        private readonly SettlementAdjustmentEngine $adjustments,
        private readonly SettlementTotalsCalculator $totalsCalculator,
    ) {
    }

    public function currentCleaningAmount(StaffSettlementModel $settlement): float
    {
        $amount = StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->value('amount');

        return round(abs((float) ($amount ?? 0)), 2);
    }

    public function apply(
        StaffSettlementModel $settlement,
        float $cleaningAmount,
        int $userId,
    ): ?StaffSettlementAdjustmentModel {
        $this->assertSettlementCanReceiveCleaning($settlement);
        $this->assertValidAmount($cleaningAmount);

        $gross = round((float) ($settlement->gross_amount ?? 0), 2);

        if ($cleaningAmount - 0.009 > $gross) {
            throw StaffSettlementDomainException::manualCleaningExceedsGross();
        }

        $otherAdjustments = $this->existingAdjustmentsTotal((int) $settlement->id, excludeCleaning: true);
        $net = round($gross + $otherAdjustments - abs($cleaningAmount), 2);

        if ($net + 0.009 < 0) {
            throw StaffSettlementDomainException::manualCleaningExceedsAvailable();
        }

        if (abs($cleaningAmount) < 0.0001) {
            $this->adjustments->removeCleaningDeduction((int) $settlement->id);
            $this->totalsCalculator->recalculate((int) $settlement->id);

            return null;
        }

        $adjustment = $this->adjustments->upsertManualCleaningDeduction(
            settlement: $settlement,
            grossAmount: $gross,
            cleaningAmount: $cleaningAmount,
            userId: $userId,
            notes: 'Cobro de limpieza manual',
        );

        $this->totalsCalculator->recalculate((int) $settlement->id);

        return $adjustment;
    }

    private function assertSettlementCanReceiveCleaning(StaffSettlementModel $settlement): void
    {
        if ($settlement->status !== 'PENDING') {
            throw StaffSettlementDomainException::cannotModifyPendingOnly();
        }

        if ($settlement->settlement_type !== 'GIRL') {
            throw StaffSettlementDomainException::manualCleaningOnlyForGirls();
        }
    }

    private function assertValidAmount(float $cleaningAmount): void
    {
        if ($cleaningAmount < 0) {
            throw StaffSettlementDomainException::invalidManualCleaningAmount();
        }
    }

    private function existingAdjustmentsTotal(int $settlementId, bool $excludeCleaning): float
    {
        $query = StaffSettlementAdjustmentModel::query()->where('staff_settlement_id', $settlementId);

        if ($excludeCleaning) {
            $query->where('adjustment_type', '!=', SettlementAdjustmentType::CleaningDeduction->value);
        }

        return round((float) $query->sum('amount'), 2);
    }
}