<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Collection;

final class CashSessionPersonnelLedgerService
{
    /**
     * @return array{
     *   girls: list<array<string, mixed>>,
     *   waiters: list<array<string, mixed>>,
     *   cleaning: list<array<string, mixed>>,
     *   totals: array<string, string>
     * }
     */
    public function build(int $tenantId, int $branchId, int $cashSessionId): array
    {
        $session = CashSessionModel::query()
            ->where('id', $cashSessionId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        if ($session === null) {
            return [
                'girls' => [],
                'waiters' => [],
                'cleaning' => [],
                'totals' => $this->emptyTotals(),
            ];
        }

        $confirmed = $this->buildConfirmedRows($tenantId, $branchId, $cashSessionId);
        $provisional = $this->buildProvisionalRows($tenantId, $branchId, $session);

        $girls = $this->mergeRows('GIRL', $confirmed['girls'], $provisional['girls']);
        $waiters = $this->mergeRows('WAITER', $confirmed['waiters'], $provisional['waiters']);
        $cleaning = $this->mergeRows('CLEANING', $confirmed['cleaning'], $provisional['cleaning']);

        return [
            'girls' => $this->sortRows($girls),
            'waiters' => $this->sortRows($waiters),
            'cleaning' => $this->sortRows($cleaning),
            'totals' => $this->buildTotals($girls, $waiters, $cleaning),
        ];
    }

    /**
     * @return array{girls: array<int, array<string, mixed>>, waiters: array<int, array<string, mixed>>, cleaning: array<int, array<string, mixed>>}
     */
    private function buildConfirmedRows(int $tenantId, int $branchId, int $cashSessionId): array
    {
        $rows = [
            'girls' => [],
            'waiters' => [],
            'cleaning' => [],
        ];

        $settlements = StaffSettlementModel::query()
            ->with(['items', 'staffUser'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId)
            ->whereIn('status', ['PENDING', 'PAID'])
            ->orderBy('id')
            ->get();

        $waiterProfiles = StaffProfileModel::query()
            ->where('tenant_id', $tenantId)
            ->where('staff_role', 'WAITER')
            ->get(['user_id', 'waiter_commission_percent'])
            ->keyBy('user_id');

        foreach ($settlements as $settlement) {
            $roleKey = match ((string) $settlement->settlement_type) {
                'GIRL' => 'girls',
                'WAITER' => 'waiters',
                'CLEANING' => 'cleaning',
                default => null,
            };

            if ($roleKey === null) {
                continue;
            }

            $staffUserId = (int) $settlement->staff_user_id;
            $row = $rows[$roleKey][$staffUserId] ?? $this->baseRow(
                settlementType: (string) $settlement->settlement_type,
                staffUserId: $staffUserId,
                staffName: (string) ($settlement->staffUser?->name ?? '—'),
                cashSessionId: $cashSessionId,
            );

            $row['primary_settlement_id'] ??= (int) $settlement->id;
            $row['settlement_ids'][] = (int) $settlement->id;
            $row['official_shift_ids'][] = $settlement->official_shift_id !== null ? (int) $settlement->official_shift_id : null;
            $row['payment_statuses'][] = (string) $settlement->status;

            if ($roleKey === 'waiters') {
                $profile = $waiterProfiles->get($staffUserId);
                $row['compensation_mode'] = $settlement->compensation_mode;
                $row['manual_amount_input'] = $settlement->manual_amount_input !== null
                    ? number_format((float) $settlement->manual_amount_input, 2, '.', '')
                    : null;
                $row['compensation_notes'] = $settlement->compensation_notes;
                $row['commission_percent'] = $profile?->waiter_commission_percent !== null
                    ? number_format((float) $profile->waiter_commission_percent, 2, '.', '')
                    : ($row['commission_percent'] ?? null);
            }

            if ((string) $settlement->status === 'PENDING') {
                $row['confirmed_total_amount'] += (float) ($settlement->net_amount ?? $settlement->total_amount ?? 0);
                $row['adjustments_total'] += (float) ($settlement->adjustments_total ?? 0);
                $this->consumeConfirmedItems($row, $roleKey, $settlement->items);
            } else {
                $row['paid_total_amount'] += (float) ($settlement->net_amount ?? $settlement->total_amount ?? 0);
            }

            $rows[$roleKey][$staffUserId] = $row;
        }

        $salesByWaiter = SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId)
            ->whereNotNull('waiter_user_id')
            ->selectRaw('waiter_user_id, COUNT(*) AS cnt, SUM(total) AS total_amount')
            ->groupBy('waiter_user_id')
            ->get();

        foreach ($salesByWaiter as $salesRow) {
            $staffUserId = (int) $salesRow->waiter_user_id;
            $userName = (string) (UserModel::query()->whereKey($staffUserId)->value('name') ?? '—');
            $profile = $waiterProfiles->get($staffUserId);
            $row = $rows['waiters'][$staffUserId] ?? $this->baseRow('WAITER', $staffUserId, $userName, $cashSessionId);
            $row['sales_count'] = (int) $salesRow->cnt;
            $row['sales_total_amount'] = (float) $salesRow->total_amount;
            if ($profile?->waiter_commission_percent !== null) {
                $row['commission_percent'] = number_format((float) $profile->waiter_commission_percent, 2, '.', '');
            }
            $rows['waiters'][$staffUserId] = $row;
        }

        return $rows;
    }

    /**
     * @return array{girls: array<int, array<string, mixed>>, waiters: array<int, array<string, mixed>>, cleaning: array<int, array<string, mixed>>}
     */
    private function buildProvisionalRows(int $tenantId, int $branchId, CashSessionModel $session): array
    {
        $rows = [
            'girls' => [],
            'waiters' => [],
            'cleaning' => [],
        ];

        $ordersWithSaleIds = SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $orders = OrderModel::query()
            ->with('items')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
            ->whereNull('cancelled_at')
            ->where('created_at', '>=', $session->opened_at)
            ->when($session->closed_at !== null, fn ($query) => $query->where('created_at', '<=', $session->closed_at))
            ->when($ordersWithSaleIds !== [], fn ($query) => $query->whereNotIn('id', $ordersWithSaleIds))
            ->orderBy('id')
            ->get();

        $orderItemIds = $orders
            ->flatMap(fn (OrderModel $order) => $order->items->pluck('id'))
            ->map(static fn ($id) => (int) $id)
            ->values();

        $allocationsByItemId = OrderItemAllocationModel::query()
            ->whereIn('order_item_id', $orderItemIds->all())
            ->orderBy('id')
            ->get()
            ->groupBy('order_item_id');

        $waiterProfiles = StaffProfileModel::query()
            ->where('tenant_id', $tenantId)
            ->where('staff_role', 'WAITER')
            ->get(['user_id', 'waiter_commission_percent'])
            ->keyBy('user_id');

        foreach ($orders as $order) {
            if ($order->waiter_user_id !== null && $order->source_type === null && (float) $order->total > 0) {
                $waiterUserId = (int) $order->waiter_user_id;
                $waiterName = (string) (UserModel::query()->whereKey($waiterUserId)->value('name') ?? '—');
                $row = $rows['waiters'][$waiterUserId] ?? $this->baseRow('WAITER', $waiterUserId, $waiterName, (int) $session->id);
                $row['provisional_sales_count'] += 1;
                $row['provisional_sales_total_amount'] += (float) $order->total;
                $row['details'][] = $this->detailRow(
                    sourceType: 'ORDER',
                    sourceId: (int) $order->id,
                    amount: (float) $order->total,
                    cashSessionId: (int) $session->id,
                    officialShiftId: $order->official_shift_id !== null ? (int) $order->official_shift_id : null,
                    confirmed: false,
                    provisional: true,
                    sourceStatus: (string) $order->status,
                    description: 'Comanda '.$order->order_number,
                    registeredAt: $order->created_at?->format('Y-m-d H:i:s'),
                    staffUserId: $waiterUserId,
                );
                $row['official_shift_ids'][] = $order->official_shift_id !== null ? (int) $order->official_shift_id : null;
                $profile = $waiterProfiles->get($waiterUserId);
                if ($profile?->waiter_commission_percent !== null) {
                    $row['commission_percent'] = number_format((float) $profile->waiter_commission_percent, 2, '.', '');
                }
                $rows['waiters'][$waiterUserId] = $row;
            }

            foreach ($order->items as $item) {
                if ($item->cancelled_at !== null || (string) $item->item_status === 'CANCELLED') {
                    continue;
                }

                if ((string) $item->sale_mode === 'CON_ACOMPANANTE' && $item->girl_user_id !== null && (float) ($item->girl_amount ?? 0) > 0) {
                    $girlUserId = (int) $item->girl_user_id;
                    $girlName = (string) (UserModel::query()->whereKey($girlUserId)->value('name') ?? '—');
                    $row = $rows['girls'][$girlUserId] ?? $this->baseRow('GIRL', $girlUserId, $girlName, (int) $session->id);
                    $row['provisional_consumption_total'] += (float) $item->girl_amount;
                    $row['provisional_total_amount'] += (float) $item->girl_amount;
                    $row['details'][] = $this->detailRow(
                        sourceType: 'PROVISIONAL_ORDER_ITEM',
                        sourceId: (int) $item->id,
                        amount: (float) $item->girl_amount,
                        cashSessionId: (int) $session->id,
                        officialShiftId: $order->official_shift_id !== null ? (int) $order->official_shift_id : null,
                        confirmed: false,
                        provisional: true,
                        sourceStatus: (string) $order->status,
                        description: (string) $item->product_name,
                        registeredAt: $item->created_at?->format('Y-m-d H:i:s'),
                        staffUserId: $girlUserId,
                    );
                    $row['official_shift_ids'][] = $order->official_shift_id !== null ? (int) $order->official_shift_id : null;
                    $rows['girls'][$girlUserId] = $row;
                }

                /** @var Collection<int, OrderItemAllocationModel> $allocations */
                $allocations = $allocationsByItemId->get((int) $item->id, collect());
                foreach ($allocations as $allocation) {
                    $girlUserId = (int) $allocation->girl_user_id;
                    $girlName = (string) ($allocation->girl?->name ?? UserModel::query()->whereKey($girlUserId)->value('name') ?? '—');
                    $row = $rows['girls'][$girlUserId] ?? $this->baseRow('GIRL', $girlUserId, $girlName, (int) $session->id);
                    $row['provisional_bracelets_total'] += (float) $allocation->total_amount;
                    $row['provisional_total_amount'] += (float) $allocation->total_amount;
                    $row['details'][] = $this->detailRow(
                        sourceType: 'PROVISIONAL_ORDER_ALLOCATION',
                        sourceId: (int) $allocation->id,
                        amount: (float) $allocation->total_amount,
                        cashSessionId: (int) $session->id,
                        officialShiftId: $order->official_shift_id !== null ? (int) $order->official_shift_id : null,
                        confirmed: false,
                        provisional: true,
                        sourceStatus: (string) $order->status,
                        description: (string) $item->product_name,
                        registeredAt: $allocation->created_at?->format('Y-m-d H:i:s'),
                        staffUserId: $girlUserId,
                    );
                    $row['official_shift_ids'][] = $order->official_shift_id !== null ? (int) $order->official_shift_id : null;
                    $rows['girls'][$girlUserId] = $row;
                }
            }
        }

        $activeRooms = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', (int) $session->id)
            ->whereIn('status', ['ACTIVE', 'DUE'])
            ->whereNotNull('girl_user_id')
            ->get();

        foreach ($activeRooms as $room) {
            $girlUserId = (int) $room->girl_user_id;
            $girlName = (string) (UserModel::query()->whereKey($girlUserId)->value('name') ?? '—');
            $row = $rows['girls'][$girlUserId] ?? $this->baseRow('GIRL', $girlUserId, $girlName, (int) $session->id);
            $amount = (float) ($room->girl_amount ?? $room->total_amount ?? 0);
            $row['provisional_pieces_total'] += $amount;
            $row['provisional_total_amount'] += $amount;
            $row['details'][] = $this->detailRow(
                sourceType: 'PROVISIONAL_ROOM_SERVICE',
                sourceId: (int) $room->id,
                amount: $amount,
                cashSessionId: (int) $session->id,
                officialShiftId: $room->official_shift_id !== null ? (int) $room->official_shift_id : null,
                confirmed: false,
                provisional: true,
                sourceStatus: (string) $room->status,
                description: 'Pieza '.($room->room_label ?? $room->room_number ?? $room->id),
                registeredAt: $room->registered_at?->format('Y-m-d H:i:s'),
                staffUserId: $girlUserId,
            );
            $row['official_shift_ids'][] = $room->official_shift_id !== null ? (int) $room->official_shift_id : null;
            $rows['girls'][$girlUserId] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $confirmedRows
     * @param  array<int, array<string, mixed>>  $provisionalRows
     * @return list<array<string, mixed>>
     */
    private function mergeRows(string $settlementType, array $confirmedRows, array $provisionalRows): array
    {
        $merged = $confirmedRows;

        foreach ($provisionalRows as $staffUserId => $provisionalRow) {
            if (! isset($merged[$staffUserId])) {
                $merged[$staffUserId] = $provisionalRow;
                continue;
            }

            $row = $merged[$staffUserId];
            foreach ([
                'provisional_total_amount',
                'provisional_consumption_total',
                'provisional_bracelets_total',
                'provisional_pieces_total',
                'provisional_shows_total',
                'provisional_sales_total_amount',
            ] as $field) {
                $row[$field] += (float) ($provisionalRow[$field] ?? 0);
            }
            $row['provisional_sales_count'] += (int) ($provisionalRow['provisional_sales_count'] ?? 0);
            $row['details'] = array_merge($row['details'], $provisionalRow['details'] ?? []);
            $row['official_shift_ids'] = array_merge($row['official_shift_ids'], $provisionalRow['official_shift_ids'] ?? []);
            if ($row['commission_percent'] === null && $provisionalRow['commission_percent'] !== null) {
                $row['commission_percent'] = $provisionalRow['commission_percent'];
            }
            $merged[$staffUserId] = $row;
        }

        return array_map(fn (array $row) => $this->finalizeRow($row, $settlementType), $merged);
    }

    /**
     * @param  Collection<int, StaffSettlementItemModel>  $items
     */
    private function consumeConfirmedItems(array &$row, string $roleKey, Collection $items): void
    {
        foreach ($items as $item) {
            $amount = (float) $item->amount;

            if ($roleKey === 'girls') {
                match ((string) $item->source_type) {
                    'GIRL_CONSUMPTION' => $row['consumption_total'] += $amount,
                    'GIRL_BRACELET', 'GIRL_BRACELET_ALLOCATION' => $row['bracelets_total'] += $amount,
                    'GIRL_ROOM' => $row['pieces_total'] += $amount,
                    'GIRL_SHOW' => $row['shows_total'] += $amount,
                    default => null,
                };
            }

            if ($roleKey === 'cleaning') {
                match ((string) $item->source_type) {
                    'CLEANING_BASE' => $row['cleaning_base_total'] += $amount,
                    'CLEANING_ROOM' => $row['cleaning_rooms_total'] += $amount,
                    default => null,
                };
            }

            $row['details'][] = $this->detailRow(
                sourceType: (string) $item->source_type,
                sourceId: $item->source_id !== null ? (int) $item->source_id : (int) $item->id,
                amount: $amount,
                cashSessionId: (int) $row['cash_session_id'],
                officialShiftId: $item->official_shift_id !== null ? (int) $item->official_shift_id : null,
                confirmed: true,
                provisional: false,
                sourceStatus: 'CONFIRMED',
                description: (string) $item->description,
                registeredAt: $item->created_at?->format('Y-m-d H:i:s'),
                staffUserId: (int) $row['staff_user_id'],
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function baseRow(string $settlementType, int $staffUserId, string $staffName, int $cashSessionId): array
    {
        return [
            'id' => null,
            'primary_settlement_id' => null,
            'settlement_ids' => [],
            'staff_user_id' => $staffUserId,
            'staff_name' => $staffName,
            'staff_role' => $settlementType,
            'settlement_type' => $settlementType,
            'cash_session_id' => $cashSessionId,
            'official_shift_id' => null,
            'official_shift_ids' => [],
            'payment_statuses' => [],
            'consumption_total' => 0.0,
            'bracelets_total' => 0.0,
            'pieces_total' => 0.0,
            'shows_total' => 0.0,
            'cleaning_base_total' => 0.0,
            'cleaning_rooms_total' => 0.0,
            'adjustments_total' => 0.0,
            'confirmed_total_amount' => 0.0,
            'paid_total_amount' => 0.0,
            'provisional_total_amount' => 0.0,
            'provisional_consumption_total' => 0.0,
            'provisional_bracelets_total' => 0.0,
            'provisional_pieces_total' => 0.0,
            'provisional_shows_total' => 0.0,
            'sales_count' => 0,
            'sales_total_amount' => 0.0,
            'provisional_sales_count' => 0,
            'provisional_sales_total_amount' => 0.0,
            'commission_percent' => null,
            'compensation_mode' => null,
            'manual_amount_input' => null,
            'compensation_notes' => null,
            'details' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function finalizeRow(array $row, string $settlementType): array
    {
        $officialShiftIds = collect($row['official_shift_ids'])
            ->filter(static fn ($id) => $id !== null)
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $status = $this->resolveRowStatus($row);
        $totalPayable = (float) $row['confirmed_total_amount'];
        $row['id'] = $row['primary_settlement_id'];
        $row['official_shift_ids'] = $officialShiftIds;
        $row['official_shift_id'] = $officialShiftIds[0] ?? null;
        $row['status'] = $status;
        $row['status_label'] = $this->statusLabel($status);
        $row['total_amount'] = number_format($totalPayable, 2, '.', '');
        $row['net_amount'] = number_format($totalPayable, 2, '.', '');
        $row['gross_amount'] = number_format($totalPayable + (float) $row['adjustments_total'], 2, '.', '');
        $row['adjustments_total'] = number_format((float) $row['adjustments_total'], 2, '.', '');
        $row['consumption_total'] = number_format((float) $row['consumption_total'], 2, '.', '');
        $row['bracelets_total'] = number_format((float) $row['bracelets_total'], 2, '.', '');
        $row['pieces_total'] = number_format((float) $row['pieces_total'], 2, '.', '');
        $row['shows_total'] = number_format((float) $row['shows_total'], 2, '.', '');
        $row['paid_total_amount'] = number_format((float) $row['paid_total_amount'], 2, '.', '');
        $row['provisional_total_amount'] = number_format((float) $row['provisional_total_amount'], 2, '.', '');
        $row['provisional_consumption_total'] = number_format((float) $row['provisional_consumption_total'], 2, '.', '');
        $row['provisional_bracelets_total'] = number_format((float) $row['provisional_bracelets_total'], 2, '.', '');
        $row['provisional_pieces_total'] = number_format((float) $row['provisional_pieces_total'], 2, '.', '');
        $row['provisional_shows_total'] = number_format((float) $row['provisional_shows_total'], 2, '.', '');
        $row['sales_total_amount'] = number_format((float) $row['sales_total_amount'], 2, '.', '');
        $row['provisional_sales_total_amount'] = number_format((float) $row['provisional_sales_total_amount'], 2, '.', '');
        $row['cleaning_base_total'] = number_format((float) $row['cleaning_base_total'], 2, '.', '');
        $row['cleaning_rooms_total'] = number_format((float) $row['cleaning_rooms_total'], 2, '.', '');
        $row['requires_manual_amount'] = $settlementType === 'WAITER'
            && ($row['commission_percent'] === null || (float) ($row['commission_percent'] ?? 0) <= 0)
            && ((float) $row['sales_total_amount'] > 0 || (float) $row['provisional_sales_total_amount'] > 0)
            && ($row['manual_amount_input'] === null || (float) $row['manual_amount_input'] <= 0);
        $row['details'] = collect($row['details'])
            ->sortBy(['provisional', 'registered_at', 'source_type'])
            ->values()
            ->all();

        return $row;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function sortRows(array $rows): array
    {
        usort($rows, static function (array $left, array $right): int {
            return strcmp((string) ($left['staff_name'] ?? ''), (string) ($right['staff_name'] ?? ''));
        });

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function buildTotals(array $girls, array $waiters, array $cleaning): array
    {
        $sum = static fn (array $rows, string $field): float => array_reduce(
            $rows,
            static fn (float $acc, array $row): float => $acc + (float) ($row[$field] ?? 0),
            0.0,
        );

        return [
            'girls_confirmed_total' => number_format($sum($girls, 'total_amount'), 2, '.', ''),
            'girls_provisional_total' => number_format($sum($girls, 'provisional_total_amount'), 2, '.', ''),
            'waiters_confirmed_total' => number_format($sum($waiters, 'total_amount'), 2, '.', ''),
            'waiters_confirmed_sales_total' => number_format($sum($waiters, 'sales_total_amount'), 2, '.', ''),
            'waiters_provisional_sales_total' => number_format($sum($waiters, 'provisional_sales_total_amount'), 2, '.', ''),
            'cleaning_confirmed_total' => number_format($sum($cleaning, 'total_amount'), 2, '.', ''),
            'cleaning_provisional_total' => number_format($sum($cleaning, 'provisional_total_amount'), 2, '.', ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function emptyTotals(): array
    {
        return [
            'girls_confirmed_total' => '0.00',
            'girls_provisional_total' => '0.00',
            'waiters_confirmed_total' => '0.00',
            'waiters_confirmed_sales_total' => '0.00',
            'waiters_provisional_sales_total' => '0.00',
            'cleaning_confirmed_total' => '0.00',
            'cleaning_provisional_total' => '0.00',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detailRow(
        string $sourceType,
        int $sourceId,
        float $amount,
        int $cashSessionId,
        ?int $officialShiftId,
        bool $confirmed,
        bool $provisional,
        string $sourceStatus,
        string $description,
        ?string $registeredAt,
        int $staffUserId,
    ): array {
        return [
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'staff_user_id' => $staffUserId,
            'amount' => number_format($amount, 2, '.', ''),
            'cash_session_id' => $cashSessionId,
            'official_shift_id' => $officialShiftId,
            'status' => $sourceStatus,
            'paid' => false,
            'confirmed' => $confirmed,
            'provisional' => $provisional,
            'description' => $description,
            'registered_at' => $registeredAt,
        ];
    }

    private function resolveRowStatus(array $row): string
    {
        $confirmed = (float) ($row['confirmed_total_amount'] ?? 0) > 0;
        $provisional = (float) ($row['provisional_total_amount'] ?? 0) > 0
            || (float) ($row['provisional_sales_total_amount'] ?? 0) > 0;
        $hasPendingSettlement = in_array('PENDING', $row['payment_statuses'] ?? [], true);
        $hasPaidSettlement = in_array('PAID', $row['payment_statuses'] ?? [], true);

        if ($confirmed && $provisional) {
            return 'MIXED';
        }

        if ($provisional && ! $confirmed) {
            return 'PROVISIONAL';
        }

        if ($hasPendingSettlement) {
            return 'PENDING';
        }

        if ($hasPaidSettlement) {
            return 'PAID';
        }

        return 'INFORMATIVE';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'MIXED' => 'Confirmado + provisional',
            'PROVISIONAL' => 'Pendiente de cobro',
            'PENDING' => 'Confirmado',
            'PAID' => 'Pagado en sistema',
            default => 'Informativo',
        };
    }
}
