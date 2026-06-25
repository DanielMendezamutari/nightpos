<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderStatusHistoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use Illuminate\Support\Carbon;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    /** @var list<string> */
    private const ACTIVE_TABLE_STATUSES = ['OPEN', 'SENT_TO_BAR', 'IN_PREPARATION', 'READY'];

    public function findById(int $id, int $tenantId): ?Order
    {
        $model = OrderModel::query()
            ->with('items')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->mapOrder($model) : null;
    }

    public function listForBranch(
        int $tenantId,
        int $branchId,
        ?string $status = null,
        ?int $officialShiftId = null,
        ?array $statuses = null,
        ?int $cashSessionId = null,
        ?int $cashierUserId = null,
        bool $includeItems = false,
    ): array {
        $query = OrderModel::query()
            ->with('waiter:id,name')
            ->withCount('items')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id');

        if ($includeItems) {
            $query->with('items');
        }

        if ($statuses !== null && $statuses !== []) {
            $query->whereIn('status', $statuses);
        }
        elseif ($status !== null) {
            $query->where('status', $status);
        }

        if ($officialShiftId !== null) {
            $query->where('official_shift_id', $officialShiftId);
        }

        if ($cashSessionId !== null) {
            $saleQuery = SaleModel::query()
                ->select('order_id')
                ->where('cash_session_id', $cashSessionId)
                ->whereNotNull('order_id');

            if ($cashierUserId !== null) {
                $saleQuery->where('cashier_user_id', $cashierUserId);
            }

            $query->whereIn('id', $saleQuery);
        }

        return $query->get()
            ->map(fn (OrderModel $model) => $this->mapOrder($model, $includeItems))
            ->all();
    }

    public function listForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?string $status = null,
        ?array $statuses = null,
        ?int $officialShiftId = null,
    ): array {
        $query = $this->waiterScope($tenantId, $branchId, $waiterUserId, $status, $statuses, $officialShiftId);

        return $query->withCount('items')
            ->orderByDesc('id')
            ->get()
            ->map(fn (OrderModel $model) => $this->mapOrder($model, false, (int) $model->items_count))
            ->all();
    }

    public function countForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?string $status = null,
        ?array $statuses = null,
        ?int $officialShiftId = null,
    ): int {
        return $this->waiterScope($tenantId, $branchId, $waiterUserId, $status, $statuses, $officialShiftId)->count();
    }

    /**
     * @param  list<string>|null  $statuses
     */
    private function waiterScope(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?string $status,
        ?array $statuses,
        ?int $officialShiftId,
    ) {
        $query = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('waiter_user_id', $waiterUserId)
            ->whereNotIn('status', ['CANCELLED']);

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($statuses !== null && $statuses !== []) {
            $query->whereIn('status', $statuses);
        }

        if ($officialShiftId !== null) {
            $query->where('official_shift_id', $officialShiftId);
        }

        return $query;
    }

    public function findActiveByServiceTable(
        int $tenantId,
        int $branchId,
        int $serviceTableId,
        ?int $officialShiftId = null,
    ): ?Order {
        $query = OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('service_table_id', $serviceTableId)
            ->whereIn('status', self::ACTIVE_TABLE_STATUSES);

        if ($officialShiftId !== null) {
            $query->where('official_shift_id', $officialShiftId);
        }

        $model = $query->orderByDesc('id')->first();

        return $model ? $this->mapOrder($model, false) : null;
    }

    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        string $orderNumber,
        ?string $tableLabel,
        ?int $serviceAreaId,
        ?int $serviceTableId,
        ?int $waiterUserId,
        int $openedByUserId,
        ?string $notes,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): Order {
        $model = OrderModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'order_number' => $orderNumber,
            'status' => 'OPEN',
            'table_label' => $tableLabel,
            'service_area_id' => $serviceAreaId,
            'service_table_id' => $serviceTableId,
            'waiter_user_id' => $waiterUserId,
            'opened_by_user_id' => $openedByUserId,
            'notes' => $notes,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'subtotal' => 0,
            'total' => 0,
            'currency' => 'BOB',
        ]);

        $this->recordStatus($model->id, 'OPEN', $openedByUserId);

        return $this->mapOrder($model->fresh(), false);
    }

    public function addItem(
        int $tenantId,
        int $branchId,
        int $orderId,
        int $productId,
        string $productName,
        string $saleMode,
        int $quantity,
        string $unitPrice,
        string $lineTotal,
        ?string $girlAmount,
        ?string $houseAmount,
        ?int $girlUserId,
        ?string $notes,
    ): OrderItem {
        $model = OrderItemModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_name' => $productName,
            'sale_mode' => $saleMode,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'girl_amount' => $girlAmount,
            'house_amount' => $houseAmount,
            'girl_user_id' => $girlUserId,
            'item_status' => 'PENDING',
            'notes' => $notes,
        ]);

        return $this->mapItem($model);
    }

    public function recalculateTotals(int $orderId): Order
    {
        $model = OrderModel::query()->with('items')->findOrFail($orderId);
        $subtotal = $model->items
            ->where('item_status', '!=', 'CANCELLED')
            ->sum(fn (OrderItemModel $item) => (float) $item->line_total);

        $model->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        return $this->mapOrder($model->fresh(['items']));
    }

    public function updateStatus(int $orderId, int $tenantId, string $status, ?int $changedByUserId): Order
    {
        $model = OrderModel::query()
            ->where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($model === null) {
            throw new OrderNotFoundException();
        }

        $payload = ['status' => $status];

        if ($status === 'SENT_TO_BAR') {
            $payload['sent_to_bar_at'] = Carbon::now();
        }

        if ($status === 'CANCELLED') {
            $payload['cancelled_at'] = Carbon::now();
        }

        $model->update($payload);
        $this->recordStatus($orderId, $status, $changedByUserId);

        return $this->mapOrder($model->fresh());
    }

    public function markItemsSentToBar(int $orderId): void
    {
        OrderItemModel::query()
            ->where('order_id', $orderId)
            ->where('item_status', 'PENDING')
            ->update(['item_status' => 'SENT']);
    }

    public function updateItemGirlUserId(int $tenantId, int $orderId, int $itemId, int $girlUserId): void
    {
        $item = OrderItemModel::query()
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($item === null) {
            throw new OrderNotFoundException();
        }

        $item->update(['girl_user_id' => $girlUserId]);
    }

    public function updateItem(
        int $tenantId,
        int $orderId,
        int $itemId,
        int $productId,
        string $productName,
        string $saleMode,
        int $quantity,
        string $unitPrice,
        string $lineTotal,
        ?string $girlAmount,
        ?string $houseAmount,
        ?int $girlUserId,
    ): void {
        $item = OrderItemModel::query()
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($item === null) {
            throw new OrderNotFoundException();
        }

        $item->update([
            'product_id' => $productId,
            'product_name' => $productName,
            'sale_mode' => $saleMode,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'girl_amount' => $girlAmount,
            'house_amount' => $houseAmount,
            'girl_user_id' => $girlUserId,
        ]);
    }

    public function removeItem(int $tenantId, int $orderId, int $itemId): void
    {
        $deleted = OrderItemModel::query()
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->where('tenant_id', $tenantId)
            ->delete();

        if ($deleted === 0) {
            throw new OrderNotFoundException();
        }
    }

    public function cancelItem(
        int $tenantId,
        int $orderId,
        int $itemId,
        string $reason,
        int $cancelledByUserId,
    ): void {
        $item = OrderItemModel::query()
            ->where('id', $itemId)
            ->where('order_id', $orderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($item === null) {
            throw new OrderNotFoundException();
        }

        $item->update([
            'item_status' => 'CANCELLED',
            'cancellation_reason' => $reason,
            'cancelled_at' => Carbon::now(),
            'cancelled_by_user_id' => $cancelledByUserId,
        ]);
    }

    public function updateHeader(
        int $tenantId,
        int $orderId,
        string $tableLabel,
        ?int $serviceAreaId,
        ?string $notes,
    ): Order {
        $model = OrderModel::query()
            ->where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($model === null) {
            throw new OrderNotFoundException();
        }

        $model->update([
            'table_label' => $tableLabel,
            'service_area_id' => $serviceAreaId,
            'notes' => $notes,
        ]);

        return $this->mapOrder($model->fresh()->load('items'));
    }

    public function nextOrderNumber(int $branchId): string
    {
        $last = OrderModel::query()
            ->where('branch_id', $branchId)
            ->where('order_number', 'like', 'C-%')
            ->orderByDesc('id')
            ->value('order_number');

        if ($last === null) {
            return 'C-0001';
        }

        $number = (int) preg_replace('/\D/', '', $last);

        return 'C-'.str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    private function recordStatus(int $orderId, string $status, ?int $userId): void
    {
        OrderStatusHistoryModel::query()->create([
            'order_id' => $orderId,
            'status' => $status,
            'changed_by_user_id' => $userId,
            'created_at' => Carbon::now(),
        ]);
    }

    private function mapOrder(OrderModel $model, bool $withItems = true, int $itemsCount = 0): Order
    {
        $items = [];

        if ($withItems && $model->relationLoaded('items')) {
            $items = $model->items->map(fn (OrderItemModel $item) => $this->mapItem($item))->all();
            $itemsCount = count($items);
        } elseif ($model->items_count !== null) {
            $itemsCount = (int) $model->items_count;
        }

        return new Order(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            officialShiftId: $model->official_shift_id !== null ? (int) $model->official_shift_id : null,
            orderNumber: $model->order_number,
            status: $model->status,
            tableLabel: $model->table_label,
            serviceAreaId: $model->service_area_id !== null ? (int) $model->service_area_id : null,
            serviceTableId: $model->service_table_id !== null ? (int) $model->service_table_id : null,
            waiterUserId: $model->waiter_user_id !== null ? (int) $model->waiter_user_id : null,
            openedByUserId: (int) $model->opened_by_user_id,
            notes: $model->notes,
            subtotal: (string) $model->subtotal,
            total: (string) $model->total,
            currency: $model->currency,
            sentToBarAt: $model->sent_to_bar_at?->toIso8601String(),
            cancelledAt: $model->cancelled_at?->toIso8601String(),
            items: $items,
            openedAt: $model->created_at?->toIso8601String(),
            itemsCount: $itemsCount,
        );
    }

    private function mapItem(OrderItemModel $model): OrderItem
    {
        return new OrderItem(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            orderId: (int) $model->order_id,
            productId: (int) $model->product_id,
            productName: $model->product_name,
            saleMode: $model->sale_mode,
            quantity: (int) $model->quantity,
            unitPrice: (string) $model->unit_price,
            lineTotal: (string) $model->line_total,
            girlAmount: $model->girl_amount !== null ? (string) $model->girl_amount : null,
            houseAmount: $model->house_amount !== null ? (string) $model->house_amount : null,
            girlUserId: $model->girl_user_id !== null ? (int) $model->girl_user_id : null,
            itemStatus: $model->item_status,
            notes: $model->notes,
            cancellationReason: $model->cancellation_reason,
            cancelledAt: $model->cancelled_at?->toIso8601String(),
        );
    }

    public function incrementBarCorrectionCount(int $orderId, int $tenantId): int
    {
        OrderModel::query()
            ->where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->increment('bar_correction_count');

        return (int) OrderModel::query()
            ->where('id', $orderId)
            ->where('tenant_id', $tenantId)
            ->value('bar_correction_count');
    }
}
