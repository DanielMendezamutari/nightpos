<?php

declare(strict_types=1);

namespace App\Application\Reports\Services;

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\SettlementAdjustmentType;

/**
 * Secciones operativas del cierre de caja — reutiliza datos persistidos (sin recalcular ventas globales).
 */
final class CashCloseReportSectionsBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function forSession(
        int $tenantId,
        int $branchId,
        int $cashSessionId,
        ?int $officialShiftId,
        string $totalSales,
    ): array {
        $salesCount = (int) SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId)
            ->count();

        $totalSalesFloat = (float) $totalSales;
        $averageTicket = $salesCount > 0
            ? number_format($totalSalesFloat / $salesCount, 2, '.', '')
            : '0.00';

        $paymentStats = $this->paymentMethodStats($cashSessionId);

        $movements = CashMovementModel::query()
            ->with('reason')
            ->where('cash_session_id', $cashSessionId)
            ->orderBy('id')
            ->get()
            ->map(static fn ($m) => [
                'movement_type' => (string) $m->movement_type,
                'amount' => number_format((float) $m->amount, 2, '.', ''),
                'payment_method' => (string) $m->payment_method,
                'reason' => $m->reason?->name ?? $m->description,
                'notes' => $m->notes,
                'created_at' => $m->created_at?->format('Y-m-d H:i:s'),
            ])
            ->all();

        $paidSettlements = StaffSettlementModel::query()
            ->with('staffUser')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId)
            ->where('status', 'PAID')
            ->orderBy('settlement_type')
            ->orderBy('staff_user_id')
            ->get();

        $settlementsPaid = $this->groupPaidSettlements($paidSettlements);

        $settlementIds = $paidSettlements->pluck('id')->all();
        $adjustments = $this->summarizeAdjustments($settlementIds);

        $pending = $this->pendingSummary($tenantId, $branchId, $officialShiftId, $cashSessionId);

        return [
            'sales' => [
                'count' => $salesCount,
                'total' => number_format($totalSalesFloat, 2, '.', ''),
                'average_ticket' => $averageTicket,
            ],
            'payment_stats' => $paymentStats,
            'movements' => $movements,
            'movements_summary' => [
                'income_total' => number_format((float) collect($movements)->where('movement_type', 'INCOME')->sum(fn ($r) => (float) $r['amount']), 2, '.', ''),
                'expense_total' => number_format((float) collect($movements)->where('movement_type', 'EXPENSE')->sum(fn ($r) => (float) $r['amount']), 2, '.', ''),
                'count' => count($movements),
            ],
            'settlements_paid' => $settlementsPaid,
            'settlement_adjustments' => $adjustments,
            'pending' => $pending,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentMethodStats(int $cashSessionId): array
    {
        $methods = [
            'CASH' => ['count' => 0, 'amount' => '0.00'],
            'QR' => ['count' => 0, 'amount' => '0.00'],
            'CARD' => ['count' => 0, 'amount' => '0.00'],
            'MIXED' => ['count' => 0, 'amount' => '0.00'],
        ];

        $sales = SaleModel::query()
            ->where('cash_session_id', $cashSessionId)
            ->get(['payment_mode', 'total']);

        foreach ($sales as $sale) {
            $mode = strtoupper((string) ($sale->payment_mode ?? 'CASH'));
            $key = $mode === 'MIXED' ? 'MIXED' : match ($mode) {
                'QR' => 'QR',
                'CARD' => 'CARD',
                default => 'CASH',
            };

            $methods[$key]['count']++;
            $methods[$key]['amount'] = number_format(
                (float) $methods[$key]['amount'] + (float) $sale->total,
                2,
                '.',
                '',
            );
        }

        return $methods;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, StaffSettlementModel>  $paidSettlements
     * @return array<string, mixed>
     */
    private function groupPaidSettlements($paidSettlements): array
    {
        $groups = [
            'WAITER' => ['label' => 'Garzones', 'count' => 0, 'total' => 0.0, 'people' => []],
            'GIRL' => ['label' => 'Chicas', 'count' => 0, 'total' => 0.0, 'people' => []],
            'CLEANING' => ['label' => 'Limpieza', 'count' => 0, 'total' => 0.0, 'people' => []],
        ];

        foreach ($paidSettlements as $settlement) {
            $type = (string) $settlement->settlement_type;
            if (! isset($groups[$type])) {
                continue;
            }

            $amount = (float) ($settlement->net_amount ?? $settlement->total_amount);
            $groups[$type]['count']++;
            $groups[$type]['total'] += $amount;
            $groups[$type]['people'][] = [
                'name' => $settlement->staffUser?->name ?? '—',
                'amount' => number_format($amount, 2, '.', ''),
            ];
        }

        $grandTotal = 0.0;
        $result = [];

        foreach ($groups as $key => $group) {
            $total = number_format($group['total'], 2, '.', '');
            $grandTotal += $group['total'];
            $result[$key] = [
                'label' => $group['label'],
                'count' => $group['count'],
                'total' => $total,
                'people' => $group['people'],
            ];
        }

        $result['grand_total'] = number_format($grandTotal, 2, '.', '');

        return $result;
    }

    /**
     * @param  list<int>  $settlementIds
     * @return array<string, mixed>
     */
    private function summarizeAdjustments(array $settlementIds): array
    {
        if ($settlementIds === []) {
            return [
                'fines' => ['count' => 0, 'amount' => '0.00'],
                'cleaning' => ['count' => 0, 'amount' => '0.00'],
                'manual_discount' => ['count' => 0, 'amount' => '0.00'],
            ];
        }

        $rows = StaffSettlementAdjustmentModel::query()
            ->whereIn('staff_settlement_id', $settlementIds)
            ->get();

        $fines = $rows->where('adjustment_type', SettlementAdjustmentType::ManualFine->value);
        $cleaning = $rows->where('adjustment_type', SettlementAdjustmentType::CleaningDeduction->value);
        $discount = $rows->where('adjustment_type', SettlementAdjustmentType::ManualDiscount->value);

        return [
            'fines' => [
                'count' => $fines->count(),
                'amount' => number_format((float) $fines->sum('amount'), 2, '.', ''),
            ],
            'cleaning' => [
                'count' => $cleaning->count(),
                'amount' => number_format((float) $cleaning->sum('amount'), 2, '.', ''),
            ],
            'manual_discount' => [
                'count' => $discount->count(),
                'amount' => number_format((float) $discount->sum('amount'), 2, '.', ''),
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function pendingSummary(int $tenantId, int $branchId, ?int $officialShiftId, int $cashSessionId): array
    {
        $pendingSettlements = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'PENDING')
            ->when($officialShiftId, fn ($q) => $q->where('official_shift_id', $officialShiftId))
            ->count();

        $pendingOrders = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNotIn('status', ['BILLED', 'CANCELLED'])
            ->when($officialShiftId, fn ($q) => $q->where('official_shift_id', $officialShiftId))
            ->count();

        $activeRooms = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNull('ended_at')
            ->when($officialShiftId, fn ($q) => $q->where('official_shift_id', $officialShiftId))
            ->count();

        return [
            'settlements' => $pendingSettlements,
            'orders' => $pendingOrders,
            'room_services' => $activeRooms,
            'shows' => 0,
        ];
    }
}
