<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Application\Order\Support\CashierChargeableOrdersScope;
use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;

final class CashSessionCloseCheckBuilder
{
    public function __construct(
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly ComboBraceletReportingService $comboReporting,
        private readonly CashierChargeableOrdersScope $chargeableOrders,
    ) {}

    /**
     * @return array{
     *     can_close: bool,
     *     blockers: list<array{type: string, code: string, count: int, message: string, route?: string}>,
     *     warnings: list<array{type: string, code: string, count: int, message: string}>,
     *     actions: list<array{label: string, route: string}>,
     *     summary: array<string, int|float|string|null>
     * }
     */
    public function build(int $tenantId, int $branchId, int $officialShiftId, ?int $cashSessionId = null): array
    {
        $activeOrders = $this->chargeableOrders->countForCashierScope($tenantId, $branchId);

        $activeRoomServices = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->count();

        $sourceCounts = $this->settlements->countShiftSources($tenantId, $branchId, $officialShiftId, $cashSessionId);
        $unsettledSources = $this->settlements->countUnsettledShiftSources($tenantId, $branchId, $officialShiftId, $cashSessionId);
        $settlementsGenerated = $this->settlements->countGeneratedSettlements($tenantId, $branchId, $officialShiftId, $cashSessionId);
        $pendingSettlements = $this->settlements->countPendingSettlements($tenantId, $branchId, $officialShiftId, $cashSessionId);
        $pendingWaiters = $this->settlements->countPendingSettlements($tenantId, $branchId, $officialShiftId, $cashSessionId, 'WAITER');
        $pendingGirls = $this->settlements->countPendingSettlements($tenantId, $branchId, $officialShiftId, $cashSessionId, 'GIRL');
        $pendingCleaning = $this->settlements->countPendingSettlements($tenantId, $branchId, $officialShiftId, $cashSessionId, 'CLEANING');
        $pendingAmount = $this->settlements->sumPendingSettlementAmount($tenantId, $branchId, $officialShiftId, $cashSessionId);

        $blockers = [];
        $actions = [];

        if ($activeOrders > 0) {
            $blockers[] = [
                'type'    => 'ACTIVE_ORDERS',
                'code'    => 'active_orders',
                'count'   => $activeOrders,
                'message' => $activeOrders === 1
                    ? 'Hay 1 comanda pendiente de cobro.'
                    : "Hay {$activeOrders} comandas pendientes de cobro.",
                'route'   => 'nightpos-cashier-orders',
            ];
            $actions[] = ['label' => 'Ir a cobrar comandas', 'route' => 'nightpos-cashier-orders'];
        }

        if ($activeRoomServices > 0) {
            $blockers[] = [
                'type'    => 'ACTIVE_ROOM_SERVICES',
                'code'    => 'active_room_services',
                'count'   => $activeRoomServices,
                'message' => $activeRoomServices === 1
                    ? 'Hay 1 pieza activa o vencida sin finalizar.'
                    : "Hay {$activeRoomServices} piezas activas o vencidas sin finalizar.",
                'route'   => 'nightpos-room-services',
            ];
            $actions[] = ['label' => 'Ir a control de piezas', 'route' => 'nightpos-room-services'];
        }

        $actions = $this->uniqueActions($actions);

        $comboFilters = ['official_shift_id' => $officialShiftId];
        if ($cashSessionId !== null) {
            $comboFilters['cash_session_id'] = $cashSessionId;
        }

        return [
            'can_close' => $blockers === [],
            'blockers'  => $blockers,
            'warnings'  => [],
            'actions'   => $actions,
            'summary'   => [
                'active_orders'                   => $activeOrders,
                'active_room_services'            => $activeRoomServices,
                'settlements_generated'           => $settlementsGenerated,
                'pending_settlements'             => $pendingSettlements,
                'generated_pending_count'         => $pendingSettlements,
                'generated_pending_amount'        => number_format($pendingAmount, 2, '.', ''),
                'pending_waiters'                 => $pendingWaiters,
                'pending_girls'                   => $pendingGirls,
                'pending_cleaning'                => $pendingCleaning,
                'unsettled_sources'               => $unsettledSources,
                'unsettled_sources_count'         => $unsettledSources,
                'already_generated_count'         => $settlementsGenerated,
                'already_generated_pending_count' => $pendingSettlements,
            ],
            'combo_bracelets' => $this->comboReporting->buildScopeSummary($tenantId, $branchId, $comboFilters),
        ];
    }

    /**
     * @param  list<array{label: string, route: string}>  $actions
     * @return list<array{label: string, route: string}>
     */
    private function uniqueActions(array $actions): array
    {
        $seen = [];
        $out = [];

        foreach ($actions as $action) {
            if (isset($seen[$action['route']])) {
                continue;
            }

            $seen[$action['route']] = true;
            $out[] = $action;
        }

        return $out;
    }
}
