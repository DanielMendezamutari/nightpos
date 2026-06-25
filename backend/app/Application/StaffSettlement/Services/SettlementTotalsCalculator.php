<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

final class SettlementTotalsCalculator
{
    public function __construct(
        private readonly SettlementAdjustmentEngine $adjustments,
    ) {
    }

    public function recalculate(int $settlementId): void
    {
        $settlement = StaffSettlementModel::query()->find($settlementId);

        if ($settlement === null) {
            return;
        }

        $gross = (float) StaffSettlementItemModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->sum('amount');

        if ($settlement->status === 'PENDING') {
            $this->adjustments->syncCleaningDeduction($settlement, $gross);
        }

        $adjustmentsTotal = (float) StaffSettlementAdjustmentModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->sum('amount');

        $net = $gross + $adjustmentsTotal;

        $grossFormatted = number_format($gross, 2, '.', '');
        $adjustmentsFormatted = number_format($adjustmentsTotal, 2, '.', '');
        $netFormatted = number_format($net, 2, '.', '');

        StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->update([
                'gross_amount' => $grossFormatted,
                'adjustments_total' => $adjustmentsFormatted,
                'net_amount' => $netFormatted,
                'total_amount' => $netFormatted,
            ]);
    }
}
