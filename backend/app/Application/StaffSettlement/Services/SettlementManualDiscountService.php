<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;

final class SettlementManualDiscountService
{
    public function __construct(
        private readonly SettlementTotalsCalculator $totalsCalculator,
    ) {
    }

    /**
     * @return array{
     *     gross_amount: string,
     *     cleaning_amount: string,
     *     discount_base: string,
     *     discount_amount: string,
     *     net_amount: string
     * }
     */
    public function preview(
        StaffSettlementModel $settlement,
        string $discountMode,
        float $discountValue,
    ): array {
        $this->assertPending($settlement);
        $this->assertValidMode($discountMode);
        $this->assertValidValue($discountMode, $discountValue);

        $gross = (float) $settlement->gross_amount;
        $cleaning = $this->cleaningAmount((int) $settlement->id);
        $base = $this->discountBase($gross, $cleaning);
        $discountAmount = $this->calculateDiscountAmount($discountMode, $discountValue, $base);
        $this->assertDiscountWithinAvailable($discountAmount, $base);

        $existingAdjustments = $this->existingAdjustmentsTotal((int) $settlement->id, excludeManualDiscount: true);
        $net = $gross + $existingAdjustments + $discountAmount;

        if ($net + 0.009 < 0) {
            throw StaffSettlementDomainException::manualDiscountExceedsAvailable();
        }

        return [
            'gross_amount' => number_format($gross, 2, '.', ''),
            'cleaning_amount' => number_format($cleaning, 2, '.', ''),
            'discount_base' => number_format($base, 2, '.', ''),
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'net_amount' => number_format($net, 2, '.', ''),
        ];
    }

    public function apply(
        StaffSettlementModel $settlement,
        string $discountMode,
        float $discountValue,
        string $reason,
        ?string $notes,
        int $userId,
    ): StaffSettlementAdjustmentModel {
        $this->assertPending($settlement);

        $reason = trim($reason);

        if ($reason === '') {
            throw StaffSettlementDomainException::manualDiscountReasonRequired();
        }

        $this->assertValidMode($discountMode);
        $this->assertValidValue($discountMode, $discountValue);

        $gross = (float) $settlement->gross_amount;
        $cleaning = $this->cleaningAmount((int) $settlement->id);
        $base = $this->discountBase($gross, $cleaning);
        $discountAmount = $this->calculateDiscountAmount($discountMode, $discountValue, $base);
        $this->assertDiscountWithinAvailable($discountAmount, $base);

        $existing = StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value)
            ->first();

        $isUpdate = $existing !== null;

        $adjustment = StaffSettlementAdjustmentModel::query()->updateOrCreate(
            [
                'staff_settlement_id' => $settlement->id,
                'adjustment_type' => SettlementAdjustmentType::ManualDiscount->value,
            ],
            [
                'tenant_id' => $settlement->tenant_id,
                'branch_id' => $settlement->branch_id,
                'amount' => number_format($discountAmount, 2, '.', ''),
                'discount_mode' => strtoupper($discountMode),
                'discount_value' => number_format($discountValue, 2, '.', ''),
                'calculation_base' => number_format($base, 2, '.', ''),
                'notes' => $this->buildNotes($reason, $notes),
                'dedup_key' => sprintf('manual_discount:%d', $settlement->id),
                'created_by_user_id' => $userId,
            ],
        );

        $this->totalsCalculator->recalculate((int) $settlement->id);

        return $adjustment->fresh() ?? $adjustment;
    }

    public function cancel(StaffSettlementModel $settlement): bool
    {
        $this->assertPending($settlement);

        $deleted = StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlement->id)
            ->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value)
            ->delete();

        if ($deleted > 0) {
            $this->totalsCalculator->recalculate((int) $settlement->id);
        }

        return $deleted > 0;
    }

    public function discountBase(float $gross, float $cleaningAmount): float
    {
        return round($gross + $cleaningAmount, 2);
    }

    private function cleaningAmount(int $settlementId): float
    {
        return (float) (StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value)
            ->value('amount') ?? 0);
    }

    private function existingAdjustmentsTotal(int $settlementId, bool $excludeManualDiscount): float
    {
        $query = StaffSettlementAdjustmentModel::query()->where('staff_settlement_id', $settlementId);

        if ($excludeManualDiscount) {
            $query->where('adjustment_type', '!=', SettlementAdjustmentType::ManualDiscount->value);
        }

        return (float) $query->sum('amount');
    }

    private function calculateDiscountAmount(string $discountMode, float $discountValue, float $base): float
    {
        if (strtoupper($discountMode) === 'PERCENT') {
            return round(-1 * abs($base) * abs($discountValue) / 100, 2);
        }

        return round(-1 * abs($discountValue), 2);
    }

    private function assertPending(StaffSettlementModel $settlement): void
    {
        if ($settlement->status !== 'PENDING') {
            throw StaffSettlementDomainException::cannotModifyPaidSettlement();
        }
    }

    private function assertValidMode(string $discountMode): void
    {
        if (! in_array(strtoupper($discountMode), ['PERCENT', 'AMOUNT'], true)) {
            throw StaffSettlementDomainException::invalidDiscountMode();
        }
    }

    private function assertValidValue(string $discountMode, float $discountValue): void
    {
        if ($discountValue <= 0) {
            throw StaffSettlementDomainException::invalidDiscountValue();
        }

        if (strtoupper($discountMode) === 'PERCENT' && $discountValue > 100) {
            throw StaffSettlementDomainException::invalidDiscountValue();
        }
    }

    private function assertDiscountWithinAvailable(float $discountAmount, float $base): void
    {
        if ($base <= 0) {
            throw StaffSettlementDomainException::manualDiscountExceedsAvailable();
        }

        if (abs($discountAmount) - 0.009 > $base) {
            throw StaffSettlementDomainException::manualDiscountExceedsAvailable();
        }
    }

    private function buildNotes(string $reason, ?string $notes): string
    {
        $notes = $notes !== null ? trim($notes) : '';

        if ($notes === '') {
            return $reason;
        }

        return $reason.' — '.$notes;
    }
}
