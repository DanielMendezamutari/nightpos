<?php

declare(strict_types=1);

namespace App\Application\ShiftConsole\UseCases;

use App\Application\Cash\Support\CashMapper;
use App\Application\Order\Support\OrderListScopeResolver;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\GirlIncome\Repositories\BraceletRepositoryInterface;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Domain\GirlIncome\Repositories\ShowRepositoryInterface;
use App\Domain\Room\Repositories\RoomRepositoryInterface;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCurrentShiftConsoleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly SaleRepositoryInterface $sales,
        private readonly RoomRepositoryInterface $rooms,
        private readonly RoomServiceRepositoryInterface $roomServices,
        private readonly BraceletRepositoryInterface $bracelets,
        private readonly ShowRepositoryInterface $shows,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('shift_console.access')) {
            throw PermissionDeniedException::forPermission('shift_console.access');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $tenantId = $tenant->id;
        $branchId = $branch->id;

        $openShift = $this->shifts->findOpenForBranch($tenantId, $branchId);
        $shiftId = $openShift?->id ?? $this->settlements->resolveOverviewShiftId($tenantId, $branchId);

        $shiftPayload = $openShift ? ShiftMapper::shift($openShift) : null;

        $session = $this->cashSessions->findOpenForUser($tenantId, $branchId, $userId);
        $cashSessionPayload = null;
        $cashTotals = $this->emptyCashTotals();

        if ($session !== null) {
            $sessionData = CashMapper::session($session);
            $salesByMethod = $this->sales->sumPaymentsByMethodForSession($session->id);
            $sessionData['sales_by_method'] = $salesByMethod;
            $sessionData['cashier_name'] = UserModel::query()->where('id', $session->openedByUserId)->value('name');
            $cashSessionPayload = $sessionData;

            $cash = (float) ($salesByMethod['cash'] ?? 0);
            $qr = (float) ($salesByMethod['qr'] ?? 0);
            $card = (float) ($salesByMethod['card'] ?? 0);
            $opening = (float) $session->openingAmount;

            $cashTotals = [
                'opening_amount' => number_format($opening, 2, '.', ''),
                'cash' => number_format($cash, 2, '.', ''),
                'qr' => number_format($qr, 2, '.', ''),
                'card' => number_format($card, 2, '.', ''),
                'total_collected' => number_format($cash + $qr + $card, 2, '.', ''),
                'expected_amount' => $session->expectedAmount ?? number_format($opening + $cash, 2, '.', ''),
            ];
        }

        $ordersSummary = $this->buildOrdersSummary($tenantId, $branchId, $shiftId);
        $roomsSummary = $this->rooms->statusSummary($tenantId, $branchId);
        $servicesSummary = $this->buildServicesSummary($tenantId, $branchId, $shiftId);
        $settlementsSummary = $this->buildSettlementsSummary($tenantId, $branchId, $shiftId);

        $cleaningRooms = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'CLEANING')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $duePieces = $this->roomServices->listDue($tenantId, $branchId);

        $canViewStaffNames = $this->staffContext->hasPermission('admin.users.list');

        $alerts = $this->buildAlerts(
            $tenantId,
            $branchId,
            $userId,
            $cleaningRooms,
            $duePieces,
            $canViewStaffNames,
        );

        $openCashSessions = CashSessionModel::query()
            ->with('opener:id,name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->orderBy('opened_at')
            ->get()
            ->map(function (CashSessionModel $session) use ($userId, $canViewStaffNames) {
                $isCurrentUser = (int) $session->opened_by_user_id === $userId;
                $cashierName = $session->opener?->name ?? 'Usuario #'.$session->opened_by_user_id;

                return [
                    'id' => (int) $session->id,
                    'opened_by_user_id' => (int) $session->opened_by_user_id,
                    'cashier_name' => ($canViewStaffNames || $isCurrentUser) ? $cashierName : 'Otra caja',
                    'opening_amount' => $session->opening_amount,
                    'expected_amount' => $session->expected_amount,
                    'opened_at' => $session->opened_at?->toIso8601String(),
                    'is_current_user' => $isCurrentUser,
                ];
            })
            ->all();

        $cards = [
            'shift_open' => $openShift !== null,
            'cash_open' => $session !== null,
            'active_orders' => $ordersSummary['counts']['active'],
            'open_orders' => $ordersSummary['counts']['open'],
            'sent_to_bar_orders' => $ordersSummary['counts']['sent_to_bar'],
            'pending_charge_orders' => $ordersSummary['counts']['pending_charge'],
            'occupied_rooms' => (int) ($roomsSummary['occupied'] ?? 0),
            'cleaning_rooms' => (int) ($roomsSummary['cleaning'] ?? 0),
            'due_room_services' => count($duePieces),
            'pending_settlements' => $settlementsSummary['pending_count'],
        ];

        return OperationResult::ok('Consola de turno.', [
            'shift' => $shiftPayload,
            'cash_session' => $cashSessionPayload,
            'cash_totals' => $cashTotals,
            'orders_summary' => $ordersSummary,
            'rooms_summary' => $roomsSummary,
            'services_summary' => $servicesSummary,
            'settlements_summary' => $settlementsSummary,
            'alerts' => $alerts,
            'open_cash_sessions' => $openCashSessions,
            'cards' => $cards,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrdersSummary(int $tenantId, int $branchId, ?int $shiftId): array
    {
        $scope = function () use ($tenantId, $branchId, $shiftId) {
            $query = OrderModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId);

            if ($shiftId !== null) {
                $query->where('official_shift_id', $shiftId);
            }

            return $query;
        };

        $counts = [
            'active' => $scope()->whereIn('status', OrderListScopeResolver::OPERATIONAL_ACTIVE)->count(),
            'open' => $scope()->where('status', 'OPEN')->count(),
            'sent_to_bar' => $scope()->where('status', 'SENT_TO_BAR')->count(),
            'pending_charge' => $scope()->where('status', 'SENT_TO_BAR')->count(),
            'billed' => $scope()->where('status', 'BILLED')->count(),
        ];

        $toBrief = static fn (OrderModel $m) => [
            'id' => (int) $m->id,
            'order_number' => $m->order_number,
            'table_label' => $m->table_label,
            'status' => $m->status,
            'total' => $m->total,
            'currency' => $m->currency,
        ];

        $openList = $scope()->where('status', 'OPEN')->orderByDesc('id')->limit(8)->get();
        $barList = $scope()->where('status', 'SENT_TO_BAR')->orderByDesc('id')->limit(8)->get();
        $chargeList = $scope()->whereIn('status', OrderListScopeResolver::PENDING_CHARGE)->orderByDesc('id')->limit(8)->get();
        $billedList = $scope()->where('status', 'BILLED')->orderByDesc('id')->limit(5)->get();

        return [
            'counts' => $counts,
            'open' => $openList->map($toBrief)->all(),
            'sent_to_bar' => $barList->map($toBrief)->all(),
            'pending_charge' => $chargeList->map($toBrief)->all(),
            'recent_billed' => $billedList->map($toBrief)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildServicesSummary(int $tenantId, int $branchId, ?int $shiftId): array
    {
        if ($shiftId === null) {
            return [
                'bracelets_count' => 0,
                'bracelets_total' => '0.00',
                'active_room_services_count' => count($this->roomServices->listActive($tenantId, $branchId)),
                'shows_count' => 0,
                'shows_total' => '0.00',
                'bracelets' => [],
                'active_room_services' => array_slice($this->roomServices->listActive($tenantId, $branchId), 0, 8),
                'shows' => [],
            ];
        }

        $braceletSummary = $this->bracelets->summarizeForShift($tenantId, $branchId, $shiftId);
        $showSummary = $this->shows->summarizeForShift($tenantId, $branchId, $shiftId);
        $bracelets = array_slice($this->bracelets->listForShift($tenantId, $branchId, $shiftId), 0, 8);
        $shows = array_slice($this->shows->listForShift($tenantId, $branchId, $shiftId), 0, 8);
        $active = array_slice($this->roomServices->listActive($tenantId, $branchId), 0, 8);

        return [
            'bracelets_count' => $braceletSummary['count'],
            'bracelets_total' => number_format($braceletSummary['total_amount'], 2, '.', ''),
            'active_room_services_count' => count($this->roomServices->listActive($tenantId, $branchId)),
            'shows_count' => $showSummary['count'],
            'shows_total' => number_format($showSummary['total_amount'], 2, '.', ''),
            'bracelets' => $bracelets,
            'active_room_services' => $active,
            'shows' => $shows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSettlementsSummary(int $tenantId, int $branchId, ?int $shiftId): array
    {
        if ($shiftId === null) {
            return [
                'pending_count' => 0,
                'pending_amount' => '0.00',
                'paid_amount' => '0.00',
            ];
        }

        $pending = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'PENDING');

        return [
            'pending_count' => (clone $pending)->count(),
            'pending_amount' => number_format((float) (clone $pending)->sum('total_amount'), 2, '.', ''),
            'paid_amount' => number_format((float) StaffSettlementModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('official_shift_id', $shiftId)
                ->where('status', 'PAID')
                ->sum('total_amount'), 2, '.', ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyCashTotals(): array
    {
        return [
            'opening_amount' => '0.00',
            'cash' => '0.00',
            'qr' => '0.00',
            'card' => '0.00',
            'total_collected' => '0.00',
            'expected_amount' => '0.00',
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, RoomModel> $cleaningRooms
     * @param list<array<string, mixed>> $duePieces
     *
     * @return list<array<string, mixed>>
     */
    private function buildAlerts(
        int $tenantId,
        int $branchId,
        int $userId,
        $cleaningRooms,
        array $duePieces,
        bool $canViewStaffNames,
    ): array {
        $alerts = [];

        if ($cleaningRooms->isNotEmpty()) {
            $alerts[] = [
                'type' => 'rooms_cleaning',
                'severity' => 'info',
                'message' => 'Habitaciones en limpieza: '.$cleaningRooms->pluck('code')->join(', '),
                'count' => $cleaningRooms->count(),
            ];
        }

        if ($duePieces !== []) {
            $alerts[] = [
                'type' => 'room_services_due',
                'severity' => 'warning',
                'message' => count($duePieces).' pieza(s) vencida(s) requieren revisión.',
                'count' => count($duePieces),
            ];
        }

        $waiters = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($query) use ($branchId) {
                $query->where('staff_role', 'WAITER')
                    ->where('status', 'active')
                    ->where(function ($inner) use ($branchId) {
                        $inner->where('branch_id', $branchId)->orWhereNull('branch_id');
                    })
                    ->where(function ($inner) {
                        $inner->whereNull('waiter_commission_percent')
                            ->orWhere('waiter_commission_percent', '<=', 0);
                    });
            })
            ->pluck('name')
            ->all();

        if ($waiters !== []) {
            $alerts[] = [
                'type' => 'waiters_without_commission',
                'severity' => 'warning',
                'message' => $canViewStaffNames
                    ? 'Garzones sin % comisión: '.implode(', ', $waiters)
                    : count($waiters).' garzón(es) sin % de comisión configurada. Contacte al administrador.',
                'count' => count($waiters),
            ];
        }

        $girls = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($query) use ($branchId) {
                $query->where('staff_role', 'GIRL')
                    ->where('status', 'active')
                    ->where('can_receive_girl_commissions', false)
                    ->where(function ($inner) use ($branchId) {
                        $inner->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
            })
            ->pluck('name')
            ->all();

        if ($girls !== []) {
            $alerts[] = [
                'type' => 'girls_without_commission_flag',
                'severity' => 'warning',
                'message' => $canViewStaffNames
                    ? 'Chicas sin flag de comisión: '.implode(', ', $girls)
                    : count($girls).' chica(s) sin flag de comisión activo. Contacte al administrador.',
                'count' => count($girls),
            ];
        }

        $otherSessions = CashSessionModel::query()
            ->with('opener:id,name')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->where('opened_by_user_id', '!=', $userId)
            ->get();

        if ($otherSessions->isNotEmpty()) {
            $count = $otherSessions->count();
            $alerts[] = [
                'type' => 'other_cash_sessions_open',
                'severity' => 'info',
                'message' => $canViewStaffNames
                    ? 'Otras cajas abiertas: '.implode(', ', $otherSessions->map(fn (CashSessionModel $s) => $s->opener?->name ?? 'Usuario #'.$s->opened_by_user_id)->unique()->values()->all())
                    : ($count === 1
                        ? 'Hay otra caja abierta en la sucursal.'
                        : "Hay {$count} otras cajas abiertas en la sucursal."),
                'count' => $count,
            ];
        }

        $productsWithoutPrice = ProductModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereDoesntHave('prices', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->where('sale_mode', 'SOLO_CLIENTE')
                    ->where('status', 'active');
            })
            ->orderBy('name')
            ->limit(10)
            ->pluck('name')
            ->all();

        if ($productsWithoutPrice !== []) {
            $alerts[] = [
                'type' => 'products_without_price',
                'severity' => 'warning',
                'message' => 'Productos sin precio SOLO_CLIENTE: '.implode(', ', $productsWithoutPrice),
                'count' => count($productsWithoutPrice),
            ];
        }

        return $alerts;
    }
}
