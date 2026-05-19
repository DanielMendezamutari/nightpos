<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class GetPosOrderController extends Controller
{
    public function __invoke(int $orderId): JsonResponse
    {
        $order = DB::table('orders')
            ->leftJoin('customer_sessions', 'customer_sessions.id', '=', 'orders.customer_session_id')
            ->where('orders.id', $orderId)
            ->select([
                'orders.id',
                'orders.status',
                'orders.shift_turn_id',
                'orders.customer_session_id',
                'orders.waiter_user_id',
                'orders.ordered_at',
                'customer_sessions.table_code',
                'customer_sessions.zone_code',
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada.'], 404);
        }

        $items = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('order_items.order_id', $orderId)
            ->orderBy('order_items.id')
            ->get([
                'order_items.id',
                'order_items.product_id',
                'products.sku',
                'products.name as product_name',
                'order_items.consumption_type',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.subtotal',
                'order_items.waiter_user_id',
                'order_items.companion_id',
            ])
            ->map(static function ($i): array {
                return [
                    'id' => (int) $i->id,
                    'product_id' => (int) $i->product_id,
                    'sku' => $i->sku,
                    'product_name' => $i->product_name,
                    'consumption_type' => $i->consumption_type,
                    'quantity' => (int) $i->quantity,
                    'unit_price' => (int) $i->unit_price,
                    'subtotal' => (int) $i->subtotal,
                    'waiter_user_id' => (int) $i->waiter_user_id,
                    'companion_id' => $i->companion_id !== null ? (int) $i->companion_id : null,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'order' => [
                    'id' => (int) $order->id,
                    'status' => $order->status,
                    'shift_turn_id' => (int) $order->shift_turn_id,
                    'customer_session_id' => (int) $order->customer_session_id,
                    'waiter_user_id' => (int) $order->waiter_user_id,
                    'table_code' => $order->table_code,
                    'zone_code' => $order->zone_code,
                    'ordered_at' => $order->ordered_at,
                ],
                'items' => $items,
            ],
        ]);
    }
}

