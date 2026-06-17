<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalePaymentModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Support\Facades\DB;

final class EloquentReportReadRepository implements ReportReadRepositoryInterface
{
    public function __construct(
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly ComboBraceletReportingService $comboReporting,
    ) {}

    public function getDailySummary(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        // Sales by payment method
        $payments = SalePaymentModel::query()
            ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('sales.official_shift_id', $shiftIds))
            ->select('sale_payments.payment_method', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('sale_payments.payment_method')
            ->pluck('total', 'payment_method');

        $totalCash = (float) ($payments['CASH'] ?? 0);
        $totalQr   = (float) ($payments['QR'] ?? 0);
        $totalCard = (float) ($payments['CARD'] ?? 0);
        $totalSales = $totalCash + $totalQr + $totalCard;

        // Services totals
        $braceletsTotal = (float) BraceletModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->sum('total_amount');

        $showsTotal = (float) ShowModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->sum('total_amount');

        $roomServicesTotal = (float) RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->sum('total_amount');

        $braceletsCount   = (int) BraceletModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))->count();
        $showsCount       = (int) ShowModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))->count();
        $roomServicesCount = (int) RoomServiceModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))->count();

        // Settlements
        $sessionIds = CashSessionModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->pluck('id');

        $settlementsQuery = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds));

        $settlementsPaid    = (float) (clone $settlementsQuery)->where('status', 'PAID')->sum('total_amount');
        $settlementsPending = (float) (clone $settlementsQuery)->where('status', 'PENDING')->sum('total_amount');

        // Cash movements
        $manualIncome = 0.0;
        $manualExpense = 0.0;
        $openingCash = 0.0;

        if ($sessionIds->isNotEmpty()) {
            $openingCash = (float) CashSessionModel::query()
                ->whereIn('id', $sessionIds)
                ->sum('opening_amount');

            $manualIncome = (float) CashMovementModel::query()
                ->whereIn('cash_session_id', $sessionIds)
                ->where('movement_type', 'INCOME')
                ->where('description', 'not like', 'Cobro comanda%')
                ->where('description', 'not like', 'Venta directa%')
                ->sum('amount');

            $manualExpense = (float) CashMovementModel::query()
                ->whereIn('cash_session_id', $sessionIds)
                ->where('movement_type', 'EXPENSE')
                ->sum('amount');
        }

        $expectedCash = $openingCash + $totalCash + $manualIncome - $manualExpense;

        // Rooms
        $roomsUsed = (int) RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->distinct('room_id')
            ->count('room_id');

        $roomsCleaning = (int) RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'CLEANING')
            ->count();

        $totalServices = $braceletsTotal + $showsTotal + $roomServicesTotal;

        $comboSummary = $this->comboReporting->buildScopeSummary($tenantId, $branchId, [
            'shift_ids' => $shiftIds,
        ]);

        return [
            'sales' => [
                'total'      => $this->fmt($totalSales),
                'total_cash' => $this->fmt($totalCash),
                'total_qr'   => $this->fmt($totalQr),
                'total_card' => $this->fmt($totalCard),
            ],
            'services' => [
                'total'               => $this->fmt($totalServices),
                'bracelets_total'     => $this->fmt($braceletsTotal),
                'bracelets_count'     => $braceletsCount,
                'shows_total'         => $this->fmt($showsTotal),
                'shows_count'         => $showsCount,
                'room_services_total' => $this->fmt($roomServicesTotal),
                'room_services_count' => $roomServicesCount,
            ],
            'settlements' => [
                'paid'    => $this->fmt($settlementsPaid),
                'pending' => $this->fmt($settlementsPending),
            ],
            'cash' => [
                'opening_cash'     => $this->fmt($openingCash),
                'manual_income'    => $this->fmt($manualIncome),
                'manual_expense'   => $this->fmt($manualExpense),
                'expected_cash'    => $this->fmt($expectedCash),
            ],
            'rooms' => [
                'used'     => $roomsUsed,
                'cleaning' => $roomsCleaning,
            ],
            'combo_bracelets' => $comboSummary,
        ];
    }

    public function getSalesReport(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        $query = SaleModel::query()
            ->with(['payments', 'items.allocations.girl', 'cashier'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->when(isset($filters['cashier_user_id']), fn ($q) => $q->where('cashier_user_id', $filters['cashier_user_id']))
            ->when(isset($filters['waiter_user_id']), fn ($q) => $q->where('waiter_user_id', $filters['waiter_user_id']))
            ->when(isset($filters['payment_method']), fn ($q) => $q->where('payment_mode', $filters['payment_method']))
            ->orderByDesc('id');

        $sales = $query->limit(500)->get();

        $productIds = $sales->flatMap(fn ($sale) => $sale->items->pluck('product_id'))->unique()->filter()->values()->all();
        $products = $this->comboReporting->loadProductsByIds(array_map('intval', $productIds));

        $rows = $sales->map(function ($sale) use ($products) {
            return [
                'id'             => $sale->id,
                'sale_number'    => $sale->sale_number,
                'type'           => $sale->order_id ? 'order' : 'direct',
                'order_id'       => $sale->order_id,
                'cashier'        => $sale->cashier?->name ?? '-',
                'payment_mode'   => $sale->payment_mode,
                'total'          => $sale->total,
                'paid_at'        => $sale->paid_at,
                'items'          => $sale->items->map(function ($item) use ($products) {
                    $base = [
                        'product_id'    => $item->product_id,
                        'product_name'  => $item->product_name_snapshot,
                        'sale_mode'     => $item->sale_mode,
                        'quantity'      => $item->quantity,
                        'unit_price'    => $item->unit_price_snapshot,
                        'line_total'    => $item->line_total,
                        'girl_amount'   => $item->girl_amount_snapshot,
                        'house_amount'  => $item->house_amount_snapshot,
                    ];

                    return $this->comboReporting->enrichSaleItemRow(
                        $base,
                        (int) $item->quantity,
                        $products->get((int) $item->product_id),
                        $item->relationLoaded('allocations') ? $item->allocations : collect(),
                    );
                }),
                'payments'       => $sale->payments->map(fn ($p) => [
                    'method' => $p->payment_method,
                    'amount' => $p->amount,
                ]),
            ];
        })->all();

        $totals = [
            'count'      => count($rows),
            'total'      => $this->fmt($sales->sum('total')),
            'by_method'  => $sales->flatMap(fn ($s) => $s->payments)->groupBy('payment_method')
                                  ->map(fn ($grp) => $this->fmt($grp->sum('amount'))),
        ];

        return ['sales' => $rows, 'totals' => $totals];
    }

    public function getCashReport(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        $sessions = CashSessionModel::query()
            ->with(['opener', 'closer', 'movements'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $rows = $sessions->map(function ($session) {
            $incomeMovements  = $session->movements->where('movement_type', 'INCOME');
            $expenseMovements = $session->movements->where('movement_type', 'EXPENSE');

            // Payment totals from sales linked to this session
            $salePayments = SalePaymentModel::query()
                ->join('sales', 'sales.id', '=', 'sale_payments.sale_id')
                ->where('sales.cash_session_id', $session->id)
                ->select('sale_payments.payment_method', DB::raw('SUM(sale_payments.amount) as total'))
                ->groupBy('sale_payments.payment_method')
                ->pluck('total', 'payment_method');

            return [
                'id'                => $session->id,
                'register'          => "Caja #{$session->id}",
                'status'            => $session->status,
                'opened_by'         => $session->opener?->name ?? '-',
                'closed_by'         => $session->closer?->name ?? '-',
                'opened_at'         => $session->opened_at,
                'closed_at'         => $session->closed_at,
                'opening_amount'    => $session->opening_amount,
                'expected_amount'   => $session->expected_amount,
                'declared_closing'  => $session->declared_closing_amount,
                'difference'        => $session->difference_amount,
                'sales_cash'        => $this->fmt((float) ($salePayments['CASH'] ?? 0)),
                'sales_qr'          => $this->fmt((float) ($salePayments['QR'] ?? 0)),
                'sales_card'        => $this->fmt((float) ($salePayments['CARD'] ?? 0)),
                'manual_income'     => $this->fmt((float) $incomeMovements->sum('amount')),
                'manual_expense'    => $this->fmt((float) $expenseMovements->sum('amount')),
                'movements'         => $session->movements->map(fn ($m) => [
                    'type'        => $m->movement_type,
                    'amount'      => $m->amount,
                    'description' => $m->description,
                    'method'      => $m->payment_method,
                    'created_at'  => $m->created_at,
                ])->values()->all(),
            ];
        })->all();

        $openCount   = collect($rows)->where('status', 'OPEN')->count();
        $closedCount = collect($rows)->where('status', 'CLOSED')->count();

        return [
            'sessions'     => $rows,
            'open_count'   => $openCount,
            'closed_count' => $closedCount,
        ];
    }

    public function getServicesReport(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        $bracelets = BraceletModel::query()
            ->with(['girl', 'waiter'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->when(isset($filters['girl_user_id']), fn ($q) => $q->where('girl_user_id', $filters['girl_user_id']))
            ->orderByDesc('id')
            ->get()
            ->map(fn ($b) => [
                'type'           => 'bracelet',
                'id'             => $b->id,
                'girl'           => $b->girl?->name ?? '-',
                'waiter'         => $b->waiter?->name ?? '-',
                'quantity'       => $b->quantity,
                'unit_price'     => $b->unit_price,
                'total_amount'   => $b->total_amount,
                'payment_method' => $b->payment_method,
                'registered_at'  => $b->registered_at,
            ])->all();

        $shows = ShowModel::query()
            ->with(['girl'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->when(isset($filters['girl_user_id']), fn ($q) => $q->where('girl_user_id', $filters['girl_user_id']))
            ->orderByDesc('id')
            ->get()
            ->map(fn ($s) => [
                'type'           => 'show',
                'id'             => $s->id,
                'girl'           => $s->girl?->name ?? '-',
                'show_type'      => $s->show_type,
                'unit_price'     => $s->unit_price,
                'total_amount'   => $s->total_amount,
                'payment_method' => $s->payment_method,
                'registered_at'  => $s->registered_at,
            ])->all();

        $roomServices = RoomServiceModel::query()
            ->with(['girl', 'cleaningUser'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->when(isset($filters['girl_user_id']), fn ($q) => $q->where('girl_user_id', $filters['girl_user_id']))
            ->orderByDesc('id')
            ->get()
            ->map(fn ($rs) => [
                'type'            => 'room_service',
                'id'              => $rs->id,
                'girl'            => $rs->girl?->name ?? '-',
                'room'            => $rs->room_label ?? $rs->room_number,
                'unit_price'      => $rs->unit_price,
                'total_amount'    => $rs->total_amount,
                'girl_amount'     => $rs->girl_amount,
                'house_amount'    => $rs->house_amount,
                'cleaning_amount' => $rs->cleaning_amount,
                'payment_method'  => $rs->payment_method,
                'status'          => $rs->status,
                'started_at'      => $rs->started_at,
                'ended_at'        => $rs->ended_at,
                'duration_minutes'=> $rs->duration_minutes,
            ])->all();

        $braceletsTotal   = (float) collect($bracelets)->sum('total_amount');
        $showsTotal       = (float) collect($shows)->sum('total_amount');
        $roomServicesTotal = (float) collect($roomServices)->sum('total_amount');

        $houseTotal   = (float) collect($roomServices)->sum('house_amount');
        $girlTotal    = (float) collect($roomServices)->sum('girl_amount');
        $cleaningTotal = (float) collect($roomServices)->sum('cleaning_amount');

        $comboAllocations = $this->scopedComboAllocationRows($tenantId, $branchId, $shiftIds, $filters);

        return [
            'bracelets'     => $bracelets,
            'shows'         => $shows,
            'room_services' => $roomServices,
            'combo_allocations' => $comboAllocations,
            'totals'        => [
                'bracelets_total'      => $this->fmt($braceletsTotal),
                'shows_total'          => $this->fmt($showsTotal),
                'room_services_total'  => $this->fmt($roomServicesTotal),
                'grand_total'          => $this->fmt($braceletsTotal + $showsTotal + $roomServicesTotal),
                'house_total'          => $this->fmt($houseTotal),
                'girl_total'           => $this->fmt($girlTotal),
                'cleaning_total'       => $this->fmt($cleaningTotal),
            ],
        ];
    }

    public function getSettlementsReport(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        $query = StaffSettlementModel::query()
            ->with(['staffUser', 'paidBy', 'items'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
            ->orderByDesc('id');

        $settlements = $query->limit(500)->get();

        $rows = $settlements->map(fn ($s) => [
            'id'             => $s->id,
            'staff'          => $s->staffUser?->name ?? '-',
            'staff_role'     => $s->staff_role,
            'settlement_type'=> $s->settlement_type,
            'total_amount'   => $s->total_amount,
            'status'         => $s->status,
            'paid_by'        => $s->paidBy?->name ?? '-',
            'paid_at'        => $s->paid_at,
            'items_count'    => $s->items->count(),
            'items'          => $s->items->map(function ($item) {
                $row = [
                    'id' => $item->id,
                    'source_type' => $item->source_type,
                    'source_id' => $item->source_id,
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'sale_id' => $item->sale_id,
                    'order_id' => $item->order_id,
                ];

                return $this->comboReporting->enrichSettlementItem($row);
            })->all(),
        ])->all();

        $paidRows    = collect($rows)->where('status', 'PAID');
        $pendingRows = collect($rows)->where('status', 'PENDING');

        return [
            'settlements'   => $rows,
            'totals'        => [
                'total_generated' => $this->fmt((float) $settlements->sum('total_amount')),
                'total_paid'      => $this->fmt((float) $paidRows->sum('total_amount')),
                'total_pending'   => $this->fmt((float) $pendingRows->sum('total_amount')),
                'by_role'         => $settlements->groupBy('staff_role')
                    ->map(fn ($grp) => $this->fmt((float) $grp->sum('total_amount'))),
                'paid_count'      => $paidRows->count(),
                'pending_count'   => $pendingRows->count(),
            ],
        ];
    }

    public function getRoomsReport(int $tenantId, int $branchId, array $filters): array
    {
        $shiftIds = $this->resolveShiftIds($tenantId, $branchId, $filters);

        $rooms = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderBy('code')
            ->get();

        $rows = $rooms->map(function ($room) use ($tenantId, $branchId, $shiftIds) {
            $services = RoomServiceModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('room_id', $room->id)
                ->when(!empty($shiftIds), fn ($q) => $q->whereIn('official_shift_id', $shiftIds))
                ->get();

            $totalIncome    = (float) $services->sum('total_amount');
            $avgDuration    = $services->isNotEmpty()
                ? round($services->whereNotNull('ended_at')->avg('duration_minutes') ?? 0, 1)
                : 0;
            $cleaningsCount = $services->whereNotNull('ended_at')->count();

            return [
                'id'             => $room->id,
                'code'           => $room->code,
                'name'           => $room->name,
                'status'         => $room->status,
                'services_count' => $services->count(),
                'total_income'   => $this->fmt($totalIncome),
                'avg_duration'   => $avgDuration,
                'cleanings'      => $cleaningsCount,
            ];
        })->all();

        return [
            'rooms'  => $rows,
            'totals' => [
                'rooms_count'       => count($rows),
                'rooms_used'        => collect($rows)->filter(fn ($r) => $r['services_count'] > 0)->count(),
                'total_income'      => $this->fmt((float) collect($rows)->sum(fn ($r) => (float) $r['total_income'])),
                'total_services'    => (int) collect($rows)->sum('services_count'),
                'rooms_in_cleaning' => collect($rows)->filter(fn ($r) => $r['status'] === 'CLEANING')->count(),
            ],
        ];
    }

    public function getShiftClosureCheck(int $tenantId, int $branchId, int $officialShiftId): array
    {
        // Blockers
        $openSessions = CashSessionModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'OPEN')
            ->count();

        $activeRoomServices = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->count();

        $activeOrders = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
            ->count();

        // Warnings
        $pendingSettlements = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'PENDING')
            ->count();

        $settlementsGenerated = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->count();

        $unsettledSources = $this->settlements->countUnsettledShiftSources($tenantId, $branchId, $officialShiftId);

        $roomsCleaning = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'CLEANING')
            ->count();

        // Cash difference check across closed sessions
        $sessionIds = CashSessionModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->pluck('id');

        $cashDifference = null;
        if ($sessionIds->isNotEmpty()) {
            $diff = (float) CashSessionModel::query()
                ->whereIn('id', $sessionIds)
                ->where('status', 'CLOSED')
                ->sum('difference_amount');
            $cashDifference = round($diff, 2);
        }

        $blockers  = [];
        $warnings  = [];

        if ($openSessions > 0) {
            $blockers[] = [
                'code'    => 'open_cash_sessions',
                'message' => "Hay {$openSessions} " . ($openSessions === 1 ? 'caja abierta' : 'cajas abiertas') . '. Ciérralas antes de cerrar el turno.',
                'count'   => $openSessions,
            ];
        }

        if ($activeRoomServices > 0) {
            $blockers[] = [
                'code'    => 'active_room_services',
                'message' => "Hay {$activeRoomServices} " . ($activeRoomServices === 1 ? 'pieza activa' : 'piezas activas') . '. Finaliza los servicios antes de cerrar.',
                'count'   => $activeRoomServices,
            ];
        }

        if ($activeOrders > 0) {
            $blockers[] = [
                'code'    => 'active_orders',
                'message' => "Hay {$activeOrders} " . ($activeOrders === 1 ? 'comanda pendiente' : 'comandas pendientes') . ' de cobro.',
                'count'   => $activeOrders,
            ];
        }

        if ($settlementsGenerated === 0) {
            $blockers[] = [
                'code'    => 'no_settlements_generated',
                'message' => 'No se han generado liquidaciones para este turno.',
                'count'   => 0,
            ];
        }

        if ($unsettledSources > 0) {
            $blockers[] = [
                'code'    => 'unsettled_settlement_sources',
                'message' => $unsettledSources === 1
                    ? 'Hay 1 fuente pendiente de generar liquidación.'
                    : "Hay {$unsettledSources} fuentes pendientes de generar liquidación.",
                'count'   => $unsettledSources,
            ];
        }

        if ($pendingSettlements > 0) {
            $warnings[] = [
                'code'    => 'pending_settlements',
                'message' => "Hay {$pendingSettlements} liquidaciones pendientes de pago.",
                'count'   => $pendingSettlements,
            ];
        }

        if ($roomsCleaning > 0) {
            $warnings[] = [
                'code'    => 'rooms_in_cleaning',
                'message' => "{$roomsCleaning} " . ($roomsCleaning === 1 ? 'habitación pendiente' : 'habitaciones pendientes') . ' de limpieza.',
                'count'   => $roomsCleaning,
            ];
        }

        if ($cashDifference !== null && abs($cashDifference) > 0.01) {
            $sign = $cashDifference > 0 ? '+' : '';
            $warnings[] = [
                'code'    => 'cash_difference',
                'message' => "Diferencia de caja detectada: {$sign}{$cashDifference}.",
                'count'   => 0,
                'amount'  => $cashDifference,
            ];
        }

        return [
            'can_close'   => empty($blockers),
            'blockers'    => $blockers,
            'warnings'    => $warnings,
            'summary'     => [
                'open_cash_sessions'   => $openSessions,
                'active_room_services' => $activeRoomServices,
                'active_orders'        => $activeOrders,
                'pending_settlements'  => $pendingSettlements,
                'unsettled_sources'    => $unsettledSources,
                'rooms_in_cleaning'    => $roomsCleaning,
                'cash_difference'      => $cashDifference,
            ],
            'combo_bracelets' => $this->comboReporting->buildScopeSummary($tenantId, $branchId, [
                'official_shift_id' => $officialShiftId,
            ]),
        ];
    }

    public function getProductReconciliation(int $tenantId, int $branchId, array $filters): array
    {
        // ── Sold side (sale_items) ───────────────────────────────────────────
        $saleQuery = SaleItemModel::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sale_items.tenant_id', $tenantId)
            ->where('sale_items.branch_id', $branchId);

        $this->applySaleScope($saleQuery, $filters);

        $soldRows = (clone $saleQuery)
            ->select(
                'sale_items.product_id',
                DB::raw('MAX(sale_items.product_name_snapshot) as product_name'),
                DB::raw('SUM(sale_items.quantity) as qty_sold'),
                DB::raw('SUM(sale_items.line_total) as total_amount'),
                DB::raw("SUM(CASE WHEN sale_items.sale_mode = 'CON_ACOMPANANTE' THEN sale_items.quantity ELSE 0 END) as companion_qty"),
                DB::raw("SUM(CASE WHEN sale_items.sale_mode <> 'CON_ACOMPANANTE' THEN sale_items.quantity ELSE 0 END) as solo_qty"),
                DB::raw('SUM(CASE WHEN sales.order_id IS NULL THEN sale_items.quantity ELSE 0 END) as direct_qty'),
                DB::raw('SUM(CASE WHEN sales.order_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as order_qty'),
                DB::raw('SUM(CASE WHEN sale_items.order_item_id IS NOT NULL THEN sale_items.quantity ELSE 0 END) as linked_qty')
            )
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        $braceletByProduct = $this->comboReporting->braceletUnitsSoldByProduct($tenantId, $branchId, $filters);

        // ── Ordered side (order_items) ───────────────────────────────────────
        $orderQuery = OrderItemModel::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.tenant_id', $tenantId)
            ->where('order_items.branch_id', $branchId);

        $this->applyOrderScope($orderQuery, $tenantId, $filters);

        $orderedRows = $orderQuery
            ->select(
                'order_items.product_id',
                DB::raw('MAX(order_items.product_name) as product_name'),
                DB::raw('SUM(order_items.quantity) as qty_ordered'),
                DB::raw("SUM(CASE WHEN orders.status = 'OPEN' AND order_items.item_status <> 'CANCELLED' THEN order_items.quantity ELSE 0 END) as open_qty"),
                DB::raw("SUM(CASE WHEN orders.status IN ('SENT_TO_BAR','IN_PREPARATION','READY') AND order_items.item_status <> 'CANCELLED' THEN order_items.quantity ELSE 0 END) as sent_qty"),
                DB::raw("SUM(CASE WHEN orders.status = 'BILLED' AND order_items.item_status <> 'CANCELLED' THEN order_items.quantity ELSE 0 END) as billed_qty"),
                DB::raw("SUM(CASE WHEN orders.status = 'CANCELLED' OR order_items.item_status = 'CANCELLED' THEN order_items.quantity ELSE 0 END) as cancelled_qty")
            )
            ->groupBy('order_items.product_id')
            ->get()
            ->keyBy('product_id');

        // ── Build per-product comparison ─────────────────────────────────────
        $productIds = $soldRows->keys()->merge($orderedRows->keys())->unique()->values();

        $sold = [];
        $ordered = [];
        $comparison = [];

        $okCount = $mismatchCount = $pendingCount = $cancelledCount = $directOnlyCount = 0;

        foreach ($productIds as $productId) {
            $s = $soldRows->get($productId);
            $o = $orderedRows->get($productId);

            $name = $s->product_name ?? $o->product_name ?? "#{$productId}";

            $qtySold    = (int) ($s->qty_sold ?? 0);
            $directQty  = (int) ($s->direct_qty ?? 0);
            $orderQty   = (int) ($s->order_qty ?? 0);
            $linkedQty  = (int) ($s->linked_qty ?? 0);

            $billedQty    = (int) ($o->billed_qty ?? 0);
            $openQty      = (int) ($o->open_qty ?? 0) + (int) ($o->sent_qty ?? 0);
            $cancelledQty = (int) ($o->cancelled_qty ?? 0);

            if ($s !== null) {
                $braceletMeta = $braceletByProduct[(int) $productId] ?? null;
                $sold[] = [
                    'product_id'           => (int) $productId,
                    'product_name'         => $s->product_name ?? $name,
                    'quantity_sold'        => $qtySold,
                    'total_amount'         => $this->fmt((float) $s->total_amount),
                    'solo_quantity'        => (int) $s->solo_qty,
                    'companion_quantity'   => (int) $s->companion_qty,
                    'direct_sale_quantity' => $directQty,
                    'order_sale_quantity'  => $orderQty,
                    'combo_quantity'       => (int) ($braceletMeta['combo_quantity'] ?? 0),
                    'bracelet_units_sold'  => (int) ($braceletMeta['bracelet_units_sold'] ?? 0),
                    'requires_allocation'  => ($braceletMeta['bracelet_units_sold'] ?? 0) > 0,
                ];
            }

            if ($o !== null) {
                $ordered[] = [
                    'product_id'            => (int) $productId,
                    'product_name'          => $o->product_name ?? $name,
                    'quantity_ordered'      => (int) $o->qty_ordered,
                    'open_quantity'         => (int) $o->open_qty,
                    'sent_to_bar_quantity'  => (int) $o->sent_qty,
                    'billed_quantity'       => $billedQty,
                    'cancelled_quantity'    => $cancelledQty,
                ];
            }

            $status = $this->resolveReconciliationStatus(
                billedQty: $billedQty,
                soldQty: $qtySold,
                linkedQty: $linkedQty,
                directQty: $directQty,
                openQty: $openQty,
                cancelledQty: $cancelledQty,
            );

            switch ($status) {
                case 'OK': $okCount++; break;
                case 'DIRECT_SALE_ONLY': $directOnlyCount++; break;
                case 'PENDING_NOT_SOLD': $pendingCount++; break;
                case 'CANCELLED': $cancelledCount++; break;
                default: $mismatchCount++; break;
            }

            $comparison[] = [
                'product_id'          => (int) $productId,
                'product_name'        => $name,
                'ordered_quantity'    => $billedQty,
                'sold_quantity'       => $qtySold,
                'difference_quantity' => $qtySold - $billedQty,
                'status'              => $status,
            ];
        }

        return [
            'sold'       => $sold,
            'ordered'    => $ordered,
            'comparison' => $comparison,
            'summary'    => [
                'total_products'       => count($comparison),
                'ok_count'             => $okCount,
                'mismatch_count'       => $mismatchCount,
                'pending_count'        => $pendingCount,
                'cancelled_count'      => $cancelledCount,
                'direct_only_count'    => $directOnlyCount,
                'has_differences'      => $mismatchCount > 0,
                'total_quantity_sold'  => (int) collect($sold)->sum('quantity_sold'),
                'total_amount_sold'    => $this->fmt((float) collect($sold)->sum(fn ($r) => (float) $r['total_amount'])),
                'total_bracelet_units' => (int) collect($sold)->sum('bracelet_units_sold'),
            ],
            'combo_bracelets' => $this->comboReporting->buildScopeSummary($tenantId, $branchId, $filters),
        ];
    }

    /**
     * @param  list<int>  $shiftIds
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function scopedComboAllocationRows(int $tenantId, int $branchId, array $shiftIds, array $filters): array
    {
        $payload = array_merge($filters, ['shift_ids' => $shiftIds]);

        return SaleItemAllocationModel::query()
            ->with(['girl', 'saleItem.sale'])
            ->where('sale_item_allocations.tenant_id', $tenantId)
            ->where('sale_item_allocations.branch_id', $branchId)
            ->join('sale_items', 'sale_items.id', '=', 'sale_item_allocations.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when(! empty($shiftIds), fn ($q) => $q->whereIn('sales.official_shift_id', $shiftIds))
            ->when(isset($filters['girl_user_id']), fn ($q) => $q->where('sale_item_allocations.girl_user_id', (int) $filters['girl_user_id']))
            ->orderByDesc('sale_item_allocations.id')
            ->select('sale_item_allocations.*')
            ->limit(500)
            ->get()
            ->map(function (SaleItemAllocationModel $row) {
                return [
                    'type' => 'combo_allocation',
                    'id' => (int) $row->id,
                    'girl' => $row->girl?->name ?? '-',
                    'product_name' => $row->saleItem?->product_name_snapshot ?? '-',
                    'units' => (int) $row->units,
                    'unit_price' => number_format((float) $row->unit_amount_snapshot, 2, '.', ''),
                    'total_amount' => number_format((float) $row->total_amount_snapshot, 2, '.', ''),
                    'sale_number' => $row->saleItem?->sale?->sale_number,
                    'order_id' => $row->saleItem?->sale?->order_id,
                    'registered_at' => $row->saleItem?->sale?->paid_at,
                ];
            })
            ->all();
    }

    private function resolveReconciliationStatus(
        int $billedQty,
        int $soldQty,
        int $linkedQty,
        int $directQty,
        int $openQty,
        int $cancelledQty,
    ): string {
        if ($soldQty === 0 && $billedQty === 0) {
            if ($openQty > 0) {
                return 'PENDING_NOT_SOLD';
            }
            if ($cancelledQty > 0) {
                return 'CANCELLED';
            }
        }

        if ($billedQty > 0 && $soldQty === 0) {
            return 'MISSING_IN_SALE';
        }

        if ($billedQty === 0 && $soldQty > 0) {
            return $directQty >= $soldQty ? 'DIRECT_SALE_ONLY' : 'SOLD_WITHOUT_ORDER';
        }

        // Both sides have quantity: reconcile order-linked sales against billed orders
        return $linkedQty === $billedQty ? 'OK' : 'QUANTITY_MISMATCH';
    }

    private function applySaleScope($query, array $filters): void
    {
        if (isset($filters['cash_session_id'])) {
            $query->where('sales.cash_session_id', (int) $filters['cash_session_id']);
        }
        if (isset($filters['official_shift_id'])) {
            $query->where('sales.official_shift_id', (int) $filters['official_shift_id']);
        }
        if (isset($filters['waiter_user_id'])) {
            $query->where('sales.waiter_user_id', (int) $filters['waiter_user_id']);
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('sales.paid_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('sales.paid_at', '<=', $filters['date_to']);
        }
    }

    private function applyOrderScope($query, int $tenantId, array $filters): void
    {
        if (isset($filters['cash_session_id'])) {
            $orderIds = SaleModel::query()
                ->where('cash_session_id', (int) $filters['cash_session_id'])
                ->whereNotNull('order_id')
                ->pluck('order_id');
            $query->whereIn('orders.id', $orderIds);
        }
        if (isset($filters['official_shift_id'])) {
            $query->where('orders.official_shift_id', (int) $filters['official_shift_id']);
        }
        if (isset($filters['waiter_user_id'])) {
            $query->where('orders.waiter_user_id', (int) $filters['waiter_user_id']);
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('orders.created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('orders.created_at', '<=', $filters['date_to']);
        }
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function resolveShiftIds(int $tenantId, int $branchId, array $filters): array
    {
        if (isset($filters['official_shift_id'])) {
            return [(int) $filters['official_shift_id']];
        }

        $query = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        if (isset($filters['date_from'])) {
            $query->where('business_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('business_date', '<=', $filters['date_to']);
        }

        $ids = $query->pluck('id')->all();

        // If no filters → default to last 30 days
        if (empty($filters) || (!isset($filters['date_from']) && !isset($filters['date_to']))) {
            $query2 = OfficialShiftModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->orderByDesc('id')
                ->limit(10);
            $ids = $query2->pluck('id')->all();
        }

        return $ids;
    }

    private function fmt(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
