<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Entities\OrderItemAllocation;
use App\Domain\Product\Entities\Product;

final class OrderMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function order(Order $order, bool $includeItems = true): array
    {
        $data = [
            'id' => $order->id,
            'tenant_id' => $order->tenantId,
            'branch_id' => $order->branchId,
            'official_shift_id' => $order->officialShiftId,
            'order_number' => $order->orderNumber,
            'status' => $order->status,
            'table_label' => $order->tableLabel,
            'service_area_id' => $order->serviceAreaId,
            'service_table_id' => $order->serviceTableId,
            'waiter_user_id' => $order->waiterUserId,
            'opened_by_user_id' => $order->openedByUserId,
            'notes' => $order->notes,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
            'currency' => $order->currency,
            'sent_to_bar_at' => $order->sentToBarAt,
            'cancelled_at' => $order->cancelledAt,
        ];

        if ($includeItems) {
            $data['items'] = array_map(static fn (OrderItem $item) => self::item($item), $order->items);
        }

        return $data;
    }

    /**
     * @param  list<OrderItemAllocation>  $allocations
     * @return array<string, mixed>
     */
    public static function item(
        OrderItem $item,
        ?Product $product = null,
        array $allocations = [],
        ?string $girlName = null,
    ): array {
        $requiresAllocation = $product?->requiresAllocation ?? false;
        $braceletUnitsPerLine = $product?->braceletUnitsPerLine ?? 1;
        $requiredUnits = $requiresAllocation
            ? $braceletUnitsPerLine * $item->quantity
            : 0;
        $allocatedUnits = array_sum(array_map(static fn (OrderItemAllocation $row) => $row->units, $allocations));

        $data = [
            'id' => $item->id,
            'product_id' => $item->productId,
            'product_name' => $item->productName,
            'sale_mode' => $item->saleMode,
            'quantity' => $item->quantity,
            'unit_price' => $item->unitPrice,
            'line_total' => $item->lineTotal,
            'girl_amount' => $item->girlAmount,
            'house_amount' => $item->houseAmount,
            'girl_user_id' => $item->girlUserId,
            'item_status' => $item->itemStatus,
            'notes' => $item->notes,
            'cancellation_reason' => $item->cancellationReason,
            'cancelled_at' => $item->cancelledAt,
            'requires_allocation' => $requiresAllocation,
            'bracelet_units_per_line' => $braceletUnitsPerLine,
            'required_bracelet_units' => $requiredUnits,
            'allocated_bracelet_units' => $allocatedUnits,
            'allocation_complete' => ! $requiresAllocation || $allocatedUnits === $requiredUnits,
            'allocations' => array_map(static fn (OrderItemAllocation $row) => self::allocation($row), $allocations),
        ];

        if (
            $item->saleMode === 'CON_ACOMPANANTE'
            && ! $requiresAllocation
            && $item->girlUserId !== null
            && $item->girlUserId > 0
        ) {
            $data['girl_name'] = $girlName;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $operational
     * @return array<string, mixed>
     */
    public static function listBrief(Order $order, ?string $waiterName = null, array $operational = []): array
    {
        $data = self::order($order, false);
        $data['items_count'] = $order->itemsCount;
        $data['opened_at'] = $order->openedAt;

        if ($waiterName !== null) {
            $data['waiter_name'] = $waiterName;
        }

        if ($operational !== []) {
            $data = array_merge($data, $operational);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function allocation(OrderItemAllocation $allocation): array
    {
        return [
            'id' => $allocation->id,
            'girl_user_id' => $allocation->girlUserId,
            'girl_name' => $allocation->girlName,
            'units' => $allocation->units,
            'unit_amount' => $allocation->unitAmount,
            'total_amount' => $allocation->totalAmount,
            'allocation_type' => $allocation->allocationType,
        ];
    }
}
