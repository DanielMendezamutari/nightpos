<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;

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
     * @return array<string, mixed>
     */
    public static function listBrief(Order $order, ?string $waiterName = null): array
    {
        $data = self::order($order, false);
        $data['items_count'] = $order->itemsCount;
        $data['opened_at'] = $order->openedAt;

        if ($waiterName !== null) {
            $data['waiter_name'] = $waiterName;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function item(OrderItem $item): array
    {
        return [
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
        ];
    }
}
