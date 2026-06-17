<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;

final class CashSessionCloseCheckBuilder
{
    public function __construct(
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly ComboBraceletReportingService $comboReporting,
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
        $activeOrders = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
            ->count();

        $activeRoomServices = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->count();

        $sourceCounts = $this->settlements->countShiftSources($tenantId, $branchId, $officialShiftId, $cashSessionId);
        $hasSettlementSources = array_sum($sourceCounts) > 0;
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

        $mustGenerate = ($settlementsGenerated === 0 && $hasSettlementSources) || $unsettledSources > 0;

        if ($mustGenerate) {
            $generateCount = max($unsettledSources, $hasSettlementSources && $settlementsGenerated === 0 ? 1 : 0);
            $blockers[] = [
                'type'    => 'SETTLEMENTS_NOT_GENERATED',
                'code'    => 'settlements_not_generated',
                'count'   => $unsettledSources > 0 ? $unsettledSources : $generateCount,
                'message' => $unsettledSources > 0
                    ? ($unsettledSources === 1
                        ? 'Debe generar liquidaciones: hay 1 fuente sin liquidar.'
                        : "Debe generar liquidaciones: hay {$unsettledSources} fuentes sin liquidar.")
                    : 'Debe generar liquidaciones para este turno/caja.',
                'route'   => 'nightpos-settlements',
            ];
            $actions[] = ['label' => 'Generar liquidaciones', 'route' => 'nightpos-settlements'];
        }

        if ($pendingSettlements > 0) {
            $blockers[] = [
                'type'    => 'SETTLEMENTS_PENDING_PAYMENT',
                'code'    => 'settlements_pending_payment',
                'count'   => $pendingSettlements,
                'message' => $pendingSettlements === 1
                    ? 'Hay 1 liquidación pendiente de pago.'
                    : "Hay {$pendingSettlements} liquidaciones pendientes de pago.",
                'route'   => 'nightpos-settlements',
            ];
            $actions[] = ['label' => 'Ir a liquidaciones', 'route' => 'nightpos-settlements'];
        }

        if ($pendingWaiters > 0) {
            $blockers[] = [
                'type'    => 'SETTLEMENTS_PENDING_PAYMENT',
                'code'    => 'pending_waiter_settlements',
                'count'   => $pendingWaiters,
                'message' => $pendingWaiters === 1
                    ? 'Hay 1 pago de garzón pendiente.'
                    : "Hay {$pendingWaiters} pagos de garzón pendientes.",
                'route'   => 'nightpos-settlements-waiters',
            ];
            $actions[] = ['label' => 'Pagar garzones', 'route' => 'nightpos-settlements-waiters'];
        }

        if ($pendingGirls > 0) {
            $blockers[] = [
                'type'    => 'SETTLEMENTS_PENDING_PAYMENT',
                'code'    => 'pending_girl_settlements',
                'count'   => $pendingGirls,
                'message' => $pendingGirls === 1
                    ? 'Hay 1 pago de chica pendiente.'
                    : "Hay {$pendingGirls} pagos de chica pendientes.",
                'route'   => 'nightpos-settlements-girls',
            ];
            $actions[] = ['label' => 'Pagar chicas', 'route' => 'nightpos-settlements-girls'];
        }

        if ($pendingCleaning > 0) {
            $blockers[] = [
                'type'    => 'SETTLEMENTS_PENDING_PAYMENT',
                'code'    => 'pending_cleaning_settlements',
                'count'   => $pendingCleaning,
                'message' => $pendingCleaning === 1
                    ? 'Hay 1 pago de limpieza pendiente.'
                    : "Hay {$pendingCleaning} pagos de limpieza pendientes.",
                'route'   => 'nightpos-settlements-cleaning',
            ];
            $actions[] = ['label' => 'Pagar limpieza', 'route' => 'nightpos-settlements-cleaning'];
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
