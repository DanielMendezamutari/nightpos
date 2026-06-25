<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;

/**
 * Snapshot operativo de venta/comisión garzón desde ítems de liquidación (sin recalcular ventas globales).
 */
final class SettlementWaiterSnapshotResolver
{
    /**
     * @param  iterable<int, StaffSettlementItemModel>  $items
     * @return array{sales_total: string, commission_percent: string|null, commission_amount: string}|null
     */
    public static function resolve(string $settlementType, string $staffRole, iterable $items): ?array
    {
        if ($settlementType !== 'WAITER' && $staffRole !== 'WAITER') {
            return null;
        }

        $salesTotal = 0.0;
        $commissionTotal = 0.0;
        $percent = null;

        foreach ($items as $item) {
            if ($item->source_type !== 'WAITER_COMMISSION') {
                continue;
            }

            $salesTotal += (float) $item->base_amount;
            $commissionTotal += (float) $item->amount;

            if ($percent === null && $item->percent !== null) {
                $percent = number_format((float) $item->percent, 2, '.', '');
            }
        }

        if ($salesTotal <= 0.0 && $commissionTotal <= 0.0) {
            return null;
        }

        return [
            'sales_total' => number_format($salesTotal, 2, '.', ''),
            'commission_percent' => $percent,
            'commission_amount' => number_format($commissionTotal, 2, '.', ''),
        ];
    }
}
