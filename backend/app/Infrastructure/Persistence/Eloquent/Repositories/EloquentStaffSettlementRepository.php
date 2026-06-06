<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Support\Facades\DB;

final class EloquentStaffSettlementRepository implements StaffSettlementRepositoryInterface
{
    public function saleItemAlreadySettled(int $saleItemId, string $sourceType): bool
    {
        return StaffSettlementItemModel::query()
            ->where('sale_item_id', $saleItemId)
            ->where('source_type', $sourceType)
            ->exists();
    }

    public function sourceAlreadySettled(int $sourceId, string $sourceType): bool
    {
        return StaffSettlementItemModel::query()
            ->where('source_id', $sourceId)
            ->where('source_type', $sourceType)
            ->exists();
    }

    public function resolveOverviewShiftId(int $tenantId, int $branchId): ?int
    {
        $openId = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->value('id');

        if ($openId !== null) {
            return (int) $openId;
        }

        $fromSettlements = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('official_shift_id')
            ->value('official_shift_id');

        if ($fromSettlements !== null) {
            return (int) $fromSettlements;
        }

        $latest = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id')
            ->value('id');

        return $latest !== null ? (int) $latest : null;
    }

    public function generateForShift(int $tenantId, int $branchId, int $officialShiftId): array
    {
        $createdItems = 0;
        $touchedSettlementIds = [];

        $saleItems = SaleItemModel::query()
            ->select([
                'sale_items.*',
                'sales.id as sale_id',
                'sales.order_id',
                'sales.waiter_user_id as sale_waiter_user_id',
                'sales.cash_session_id',
                'sales.sale_number',
            ])
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $officialShiftId)
            ->get();

        $bracelets = BraceletModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->get();

        $roomServices = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'FINISHED')
            ->get();

        $shows = ShowModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->get();

        $cleaningTasks = CleaningTaskModel::query()
            ->with(['room', 'roomService'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->where('status', 'DONE')
            ->get();

        DB::transaction(function () use (
            $tenantId,
            $branchId,
            $officialShiftId,
            $saleItems,
            $bracelets,
            $roomServices,
            $shows,
            $cleaningTasks,
            &$createdItems,
            &$touchedSettlementIds,
        ) {
            foreach ($saleItems as $line) {
                $waiterAmount = (float) ($line->waiter_commission_amount_snapshot ?? 0);

                if ($waiterAmount > 0 && $line->sale_waiter_user_id) {
                    if (! $this->saleItemAlreadySettled((int) $line->id, 'WAITER_COMMISSION')) {
                        $settlementId = $this->ensureSettlement(
                            $tenantId,
                            $branchId,
                            $officialShiftId,
                            $line->cash_session_id ? (int) $line->cash_session_id : null,
                            (int) $line->sale_waiter_user_id,
                            'WAITER',
                            'WAITER',
                        );

                        if ($this->canAddItemsToSettlement($settlementId)) {
                            $this->createItem(
                                $tenantId,
                                $branchId,
                                $settlementId,
                                (int) $line->sale_id,
                                (int) $line->id,
                                $line->order_id ? (int) $line->order_id : null,
                                null,
                                'WAITER_COMMISSION',
                                sprintf('Comisión — %s (%s)', $line->product_name_snapshot, $line->sale_number),
                                (string) $line->line_total,
                                $line->waiter_commission_percent_snapshot !== null
                                    ? (string) $line->waiter_commission_percent_snapshot
                                    : null,
                                (string) $line->waiter_commission_amount_snapshot,
                            );

                            $createdItems++;
                            $touchedSettlementIds[$settlementId] = true;
                        }
                    }
                }

                $girlAmount = (float) ($line->girl_amount_snapshot ?? 0);

                if ($girlAmount > 0 && $line->girl_user_id && $line->sale_mode === 'CON_ACOMPANANTE') {
                    if (! $this->saleItemAlreadySettled((int) $line->id, 'GIRL_CONSUMPTION')) {
                        $settlementId = $this->ensureSettlement(
                            $tenantId,
                            $branchId,
                            $officialShiftId,
                            $line->cash_session_id ? (int) $line->cash_session_id : null,
                            (int) $line->girl_user_id,
                            'GIRL',
                            'GIRL',
                        );

                        if ($this->canAddItemsToSettlement($settlementId)) {
                            $this->createItem(
                                $tenantId,
                                $branchId,
                                $settlementId,
                                (int) $line->sale_id,
                                (int) $line->id,
                                $line->order_id ? (int) $line->order_id : null,
                                null,
                                'GIRL_CONSUMPTION',
                                sprintf('Consumo con acompañante — %s (%s)', $line->product_name_snapshot, $line->sale_number),
                                (string) $line->line_total,
                                null,
                                (string) $line->girl_amount_snapshot,
                            );

                            $createdItems++;
                            $touchedSettlementIds[$settlementId] = true;
                        }
                    }
                }
            }

            foreach ($bracelets as $bracelet) {
                if ($this->sourceAlreadySettled((int) $bracelet->id, 'GIRL_BRACELET')) {
                    continue;
                }

                $settlementId = $this->ensureSettlement(
                    $tenantId,
                    $branchId,
                    $officialShiftId,
                    null,
                    (int) $bracelet->girl_user_id,
                    'GIRL',
                    'GIRL',
                );

                if (! $this->canAddItemsToSettlement($settlementId)) {
                    continue;
                }

                $this->createItem(
                    $tenantId,
                    $branchId,
                    $settlementId,
                    null,
                    null,
                    null,
                    (int) $bracelet->id,
                    'GIRL_BRACELET',
                    sprintf('Manilla — %d u. × %s BOB', $bracelet->quantity, $bracelet->unit_price),
                    (string) $bracelet->unit_price,
                    null,
                    (string) $bracelet->total_amount,
                );

                $createdItems++;
                $touchedSettlementIds[$settlementId] = true;
            }

            foreach ($roomServices as $room) {
                if ($this->sourceAlreadySettled((int) $room->id, 'GIRL_ROOM')) {
                    continue;
                }

                $settlementId = $this->ensureSettlement(
                    $tenantId,
                    $branchId,
                    $officialShiftId,
                    null,
                    (int) $room->girl_user_id,
                    'GIRL',
                    'GIRL',
                );

                if (! $this->canAddItemsToSettlement($settlementId)) {
                    continue;
                }

                $roomLabel = $room->room_label ?? ($room->room_number ? "hab. {$room->room_number}" : 'pieza');

                $girlSettlementAmount = $room->girl_amount !== null
                    ? (string) $room->girl_amount
                    : (string) $room->total_amount;

                $cleaningDeduction = (float) ($room->cleaning_amount ?? 0);
                $roomDescription = $cleaningDeduction > 0
                    ? sprintf('Pieza — %s (limpieza -%.2f)', $roomLabel, $cleaningDeduction)
                    : sprintf('Pieza — %s', $roomLabel);

                $this->createItem(
                    $tenantId,
                    $branchId,
                    $settlementId,
                    null,
                    null,
                    null,
                    (int) $room->id,
                    'GIRL_ROOM',
                    $roomDescription,
                    (string) $room->unit_price,
                    null,
                    $girlSettlementAmount,
                );

                $createdItems++;
                $touchedSettlementIds[$settlementId] = true;
            }

            foreach ($shows as $show) {
                if ($this->sourceAlreadySettled((int) $show->id, 'GIRL_SHOW')) {
                    continue;
                }

                $settlementId = $this->ensureSettlement(
                    $tenantId,
                    $branchId,
                    $officialShiftId,
                    null,
                    (int) $show->girl_user_id,
                    'GIRL',
                    'GIRL',
                );

                if (! $this->canAddItemsToSettlement($settlementId)) {
                    continue;
                }

                $this->createItem(
                    $tenantId,
                    $branchId,
                    $settlementId,
                    null,
                    null,
                    null,
                    (int) $show->id,
                    'GIRL_SHOW',
                    sprintf('Show — %s', $show->show_type),
                    (string) $show->unit_price,
                    null,
                    (string) $show->total_amount,
                );

                $createdItems++;
                $touchedSettlementIds[$settlementId] = true;
            }

            $cleaningUsersWithTasks = [];

            foreach ($cleaningTasks as $task) {
                if ($this->sourceAlreadySettled((int) $task->id, 'CLEANING_ROOM')) {
                    continue;
                }

                $settlementId = $this->ensureSettlement(
                    $tenantId,
                    $branchId,
                    $officialShiftId,
                    null,
                    (int) $task->cleaning_user_id,
                    'CLEANING',
                    'CLEANING',
                );

                if (! $this->canAddItemsToSettlement($settlementId)) {
                    continue;
                }

                $roomLabel = $task->roomService?->room_label
                    ?? $task->room?->code
                    ?? ($task->roomService?->room_number ? "hab. {$task->roomService->room_number}" : 'pieza');

                $this->createItem(
                    $tenantId,
                    $branchId,
                    $settlementId,
                    null,
                    null,
                    null,
                    (int) $task->id,
                    'CLEANING_ROOM',
                    sprintf('%s servicio #%d', $roomLabel, (int) $task->room_service_id),
                    (string) $task->amount,
                    null,
                    (string) $task->amount,
                );

                $createdItems++;
                $touchedSettlementIds[$settlementId] = true;
                $cleaningUsersWithTasks[(int) $task->cleaning_user_id] = true;
            }

            foreach (array_keys($cleaningUsersWithTasks) as $cleaningUserId) {
                $settlementId = $this->ensureSettlement(
                    $tenantId,
                    $branchId,
                    $officialShiftId,
                    null,
                    $cleaningUserId,
                    'CLEANING',
                    'CLEANING',
                );

                if (! $this->canAddItemsToSettlement($settlementId)) {
                    continue;
                }

                if ($this->settlementHasItemType($settlementId, 'CLEANING_BASE')) {
                    continue;
                }

                $profile = StaffProfileModel::query()->where('user_id', $cleaningUserId)->first();
                $baseAmount = $profile?->cleaning_base_amount !== null
                    ? (string) $profile->cleaning_base_amount
                    : number_format((float) config('nightpos.cleaning.default_base_amount', 30), 2, '.', '');

                $this->createItem(
                    $tenantId,
                    $branchId,
                    $settlementId,
                    null,
                    null,
                    null,
                    $officialShiftId,
                    'CLEANING_BASE',
                    'Base por turno',
                    $baseAmount,
                    null,
                    $baseAmount,
                );

                $createdItems++;
                $touchedSettlementIds[$settlementId] = true;
            }

            foreach (array_keys($touchedSettlementIds) as $settlementId) {
                $this->recalculateTotal((int) $settlementId);
            }
        });

        return [
            'created_items' => $createdItems,
            'settlements_touched' => count($touchedSettlementIds),
            'shift_id' => $officialShiftId,
        ];
    }

    public function getCurrentShiftOverview(int $tenantId, int $branchId, int $officialShiftId, ?int $onlyStaffUserId): array
    {
        $shift = OfficialShiftModel::query()
            ->where('id', $officialShiftId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        $settlements = $this->loadSettlementsQuery($tenantId, $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->when($onlyStaffUserId !== null, fn ($q) => $q->where('staff_user_id', $onlyStaffUserId))
            ->orderBy('settlement_type')
            ->orderBy('staff_user_id')
            ->get()
            ->map(fn (StaffSettlementModel $m) => $this->mapSettlementSummary($m))
            ->all();

        $waiters = array_values(array_filter($settlements, fn (array $s) => $s['settlement_type'] === 'WAITER'));
        $girls = array_values(array_filter($settlements, fn (array $s) => $s['settlement_type'] === 'GIRL'));
        $cleaning = array_values(array_filter($settlements, fn (array $s) => $s['settlement_type'] === 'CLEANING'));

        return [
            'shift' => $shift ? [
                'id' => $shift->id,
                'name' => $shift->name,
                'shift_type' => $shift->shift_type,
                'shift_type_label' => $shift->shift_type === 'DAY' ? 'Día' : 'Noche',
                'business_date' => $shift->business_date?->format('Y-m-d'),
                'status' => $shift->status,
            ] : null,
            'summary' => $this->buildSummary($settlements),
            'waiters' => $waiters,
            'girls' => $girls,
            'cleaning' => $cleaning,
            'settlements' => $settlements,
        ];
    }

    public function findById(int $id, int $tenantId, int $branchId, ?int $onlyStaffUserId): ?array
    {
        $model = $this->loadSettlementsQuery($tenantId, $branchId)
            ->where('staff_settlements.id', $id)
            ->when($onlyStaffUserId !== null, fn ($q) => $q->where('staff_user_id', $onlyStaffUserId))
            ->first();

        if ($model === null) {
            return null;
        }

        $items = StaffSettlementItemModel::query()
            ->where('staff_settlement_id', $model->id)
            ->orderBy('id')
            ->get()
            ->map(fn (StaffSettlementItemModel $item) => $this->mapItemDetail($item))
            ->all();

        return [
            'settlement' => $this->mapSettlementSummary($model),
            'items' => $items,
        ];
    }

    public function markPaid(int $id, int $tenantId, int $branchId, int $paidByUserId, ?string $notes): array
    {
        $model = StaffSettlementModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $model->update([
            'status' => 'PAID',
            'paid_by_user_id' => $paidByUserId,
            'paid_at' => now(),
            'notes' => $notes ?? $model->notes,
        ]);

        return $this->mapSettlementSummary($model->fresh(['staffUser', 'paidBy']));
    }

    public function listHistory(int $tenantId, int $branchId, ?int $onlyStaffUserId, array $filters, int $limit): array
    {
        $query = $this->loadSettlementsQuery($tenantId, $branchId)
            ->when($onlyStaffUserId !== null, fn ($q) => $q->where('staff_user_id', $onlyStaffUserId));

        if (! empty($filters['official_shift_id'])) {
            $query->where('official_shift_id', (int) $filters['official_shift_id']);
        }

        if (! empty($filters['staff_user_id'])) {
            $query->where('staff_user_id', (int) $filters['staff_user_id']);
        }

        if (! empty($filters['settlement_type'])) {
            $query->where('settlement_type', $filters['settlement_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereHas('officialShift', fn ($q) => $q->whereDate('business_date', '>=', $filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereHas('officialShift', fn ($q) => $q->whereDate('business_date', '<=', $filters['date_to']));
        }

        return $query
            ->orderByDesc('staff_settlements.id')
            ->limit($limit)
            ->get()
            ->map(fn (StaffSettlementModel $m) => array_merge($this->mapSettlementSummary($m), [
                'shift_name' => $m->officialShift?->name,
                'shift_business_date' => $m->officialShift?->business_date?->format('Y-m-d'),
                'shift_status' => $m->officialShift?->status,
                'paid_by_name' => $m->paidBy?->name,
            ]))
            ->all();
    }

    private function canAddItemsToSettlement(int $settlementId): bool
    {
        return StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->where('status', 'PENDING')
            ->exists();
    }

    private function settlementHasItemType(int $settlementId, string $sourceType): bool
    {
        return StaffSettlementItemModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->where('source_type', $sourceType)
            ->exists();
    }

    private function ensureSettlement(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        ?int $cashSessionId,
        int $staffUserId,
        string $staffRole,
        string $settlementType,
    ): int {
        $existing = StaffSettlementModel::query()
            ->where('official_shift_id', $officialShiftId)
            ->where('staff_user_id', $staffUserId)
            ->where('settlement_type', $settlementType)
            ->first();

        if ($existing !== null) {
            return (int) $existing->id;
        }

        $created = StaffSettlementModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_session_id' => $cashSessionId,
            'staff_user_id' => $staffUserId,
            'staff_role' => $staffRole,
            'settlement_type' => $settlementType,
            'total_amount' => 0,
            'status' => 'PENDING',
        ]);

        return (int) $created->id;
    }

    private function createItem(
        int $tenantId,
        int $branchId,
        int $settlementId,
        ?int $saleId,
        ?int $saleItemId,
        ?int $orderId,
        ?int $sourceId,
        string $sourceType,
        string $description,
        string $baseAmount,
        ?string $percent,
        string $amount,
    ): void {
        StaffSettlementItemModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'staff_settlement_id' => $settlementId,
            'sale_id' => $saleId,
            'sale_item_id' => $saleItemId,
            'order_id' => $orderId,
            'source_id' => $sourceId,
            'source_type' => $sourceType,
            'description' => $description,
            'base_amount' => $baseAmount,
            'percent' => $percent,
            'amount' => $amount,
        ]);
    }

    private function recalculateTotal(int $settlementId): void
    {
        $total = StaffSettlementItemModel::query()
            ->where('staff_settlement_id', $settlementId)
            ->sum('amount');

        StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->update(['total_amount' => $total]);
    }

    /**
     * @param  list<array<string, mixed>>  $settlements
     * @return array<string, mixed>
     */
    private function buildSummary(array $settlements): array
    {
        $waiterTotal = 0.0;
        $girlTotal = 0.0;
        $cleaningTotal = 0.0;
        $consumption = 0.0;
        $bracelets = 0.0;
        $pieces = 0.0;
        $shows = 0.0;
        $pending = 0.0;
        $paid = 0.0;

        foreach ($settlements as $row) {
            $amount = (float) $row['total_amount'];

            if ($row['settlement_type'] === 'WAITER') {
                $waiterTotal += $amount;
            }
            elseif ($row['settlement_type'] === 'CLEANING') {
                $cleaningTotal += $amount;
            }
            else {
                $girlTotal += $amount;
                $consumption += (float) ($row['consumption_total'] ?? 0);
                $bracelets += (float) ($row['bracelets_total'] ?? 0);
                $pieces += (float) ($row['pieces_total'] ?? 0);
                $shows += (float) ($row['shows_total'] ?? 0);
            }

            if ($row['status'] === 'PENDING') {
                $pending += $amount;
            }
            elseif ($row['status'] === 'PAID') {
                $paid += $amount;
            }
        }

        return [
            'total_waiters' => number_format($waiterTotal, 2, '.', ''),
            'total_girls' => number_format($girlTotal, 2, '.', ''),
            'total_cleaning' => number_format($cleaningTotal, 2, '.', ''),
            'total_consumption' => number_format($consumption, 2, '.', ''),
            'total_bracelets' => number_format($bracelets, 2, '.', ''),
            'total_pieces' => number_format($pieces, 2, '.', ''),
            'total_shows' => number_format($shows, 2, '.', ''),
            'total_pending' => number_format($pending, 2, '.', ''),
            'total_paid' => number_format($paid, 2, '.', ''),
        ];
    }

    private function loadSettlementsQuery(int $tenantId, int $branchId)
    {
        return StaffSettlementModel::query()
            ->with(['staffUser', 'officialShift', 'paidBy', 'items'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSettlementSummary(StaffSettlementModel $model): array
    {
        $items = $model->relationLoaded('items') ? $model->items : $model->items()->get();

        $consumption = 0.0;
        $bracelets = 0.0;
        $pieces = 0.0;
        $shows = 0.0;
        $cleaningBase = 0.0;
        $cleaningRooms = 0.0;
        $cleaningRoomsCount = 0;
        $cleaningRoomRate = null;
        $percent = null;
        $salesCount = $items->whereNotNull('sale_id')->unique('sale_id')->count();

        foreach ($items as $item) {
            match ($item->source_type) {
                'GIRL_CONSUMPTION' => $consumption += (float) $item->amount,
                'GIRL_BRACELET' => $bracelets += (float) $item->amount,
                'GIRL_ROOM' => $pieces += (float) $item->amount,
                'GIRL_SHOW' => $shows += (float) $item->amount,
                'CLEANING_BASE' => $cleaningBase += (float) $item->amount,
                'CLEANING_ROOM' => $cleaningRooms += (float) $item->amount,
                'WAITER_COMMISSION' => $percent ??= $item->percent !== null ? (string) $item->percent : null,
                default => null,
            };

            if ($item->source_type === 'CLEANING_ROOM') {
                $cleaningRoomsCount++;
                $cleaningRoomRate ??= number_format((float) $item->base_amount, 2, '.', '');
            }
        }

        if ($model->staff_role === 'CLEANING' && $cleaningRoomRate === null) {
            $profile = StaffProfileModel::query()->where('user_id', $model->staff_user_id)->first();
            $cleaningRoomRate = $profile?->cleaning_room_amount !== null
                ? number_format((float) $profile->cleaning_room_amount, 2, '.', '')
                : number_format((float) config('nightpos.cleaning.default_room_amount', 10), 2, '.', '');
        }

        return [
            'id' => $model->id,
            'tenant_id' => $model->tenant_id,
            'branch_id' => $model->branch_id,
            'official_shift_id' => $model->official_shift_id,
            'cash_session_id' => $model->cash_session_id,
            'staff_user_id' => $model->staff_user_id,
            'staff_name' => $model->staffUser?->name,
            'staff_role' => $model->staff_role,
            'settlement_type' => $model->settlement_type,
            'total_amount' => number_format((float) $model->total_amount, 2, '.', ''),
            'status' => $model->status,
            'paid_by_user_id' => $model->paid_by_user_id,
            'paid_at' => $model->paid_at?->format('Y-m-d H:i:s'),
            'notes' => $model->notes,
            'sales_count' => $salesCount,
            'commission_percent' => $percent,
            'consumption_total' => number_format($consumption, 2, '.', ''),
            'bracelets_total' => number_format($bracelets, 2, '.', ''),
            'pieces_total' => number_format($pieces, 2, '.', ''),
            'shows_total' => number_format($shows, 2, '.', ''),
            'cleaning_base_total' => number_format($cleaningBase, 2, '.', ''),
            'cleaning_rooms_total' => number_format($cleaningRooms, 2, '.', ''),
            'cleaning_rooms_count' => $cleaningRoomsCount,
            'cleaning_room_rate' => $cleaningRoomRate,
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapItemDetail(StaffSettlementItemModel $item): array
    {
        $saleItem = $item->saleItem;
        $sale = $item->sale_id ? SaleModel::query()->find($item->sale_id) : null;
        $registeredAt = null;

        if ($item->source_id !== null) {
            $registeredAt = match ($item->source_type) {
                'GIRL_BRACELET' => BraceletModel::query()->find($item->source_id)?->registered_at?->format('Y-m-d H:i:s'),
                'GIRL_ROOM' => RoomServiceModel::query()->find($item->source_id)?->registered_at?->format('Y-m-d H:i:s'),
                'GIRL_SHOW' => ShowModel::query()->find($item->source_id)?->registered_at?->format('Y-m-d H:i:s'),
                'CLEANING_ROOM' => CleaningTaskModel::query()->find($item->source_id)?->cleaned_at?->format('Y-m-d H:i:s'),
                default => null,
            };
        }

        if ($registeredAt === null && $sale !== null) {
            $registeredAt = $sale->created_at?->format('Y-m-d H:i:s');
        }

        return [
            'id' => $item->id,
            'staff_settlement_id' => $item->staff_settlement_id,
            'sale_id' => $item->sale_id,
            'sale_item_id' => $item->sale_item_id,
            'order_id' => $item->order_id,
            'source_id' => $item->source_id,
            'source_type' => $item->source_type,
            'description' => $item->description,
            'base_amount' => number_format((float) $item->base_amount, 2, '.', ''),
            'percent' => $item->percent !== null ? number_format((float) $item->percent, 2, '.', '') : null,
            'amount' => number_format((float) $item->amount, 2, '.', ''),
            'product_name' => $saleItem?->product_name_snapshot,
            'sale_mode' => $saleItem?->sale_mode,
            'sale_number' => $sale?->sale_number,
            'registered_at' => $registeredAt,
            'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
