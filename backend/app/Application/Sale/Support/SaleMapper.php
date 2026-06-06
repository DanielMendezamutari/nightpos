<?php

declare(strict_types=1);

namespace App\Application\Sale\Support;

use App\Domain\Sale\Entities\Sale;
use App\Domain\Sale\Entities\SaleItem;
use App\Domain\Sale\Entities\SalePayment;

final class SaleMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function sale(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'tenant_id' => $sale->tenantId,
            'branch_id' => $sale->branchId,
            'official_shift_id' => $sale->officialShiftId,
            'cash_session_id' => $sale->cashSessionId,
            'order_id' => $sale->orderId,
            'sale_number' => $sale->saleNumber,
            'cashier_user_id' => $sale->cashierUserId,
            'waiter_user_id' => $sale->waiterUserId,
            'subtotal' => $sale->subtotal,
            'total' => $sale->total,
            'currency' => $sale->currency,
            'payment_mode' => $sale->paymentMode,
            'status' => $sale->status,
            'paid_at' => $sale->paidAt,
            'items' => array_map(static fn (SaleItem $i) => self::item($i), $sale->items),
            'payments' => array_map(static fn (SalePayment $p) => self::payment($p), $sale->payments),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function item(SaleItem $item): array
    {
        return [
            'id' => $item->id,
            'product_id' => $item->productId,
            'product_name_snapshot' => $item->productNameSnapshot,
            'sale_mode' => $item->saleMode,
            'quantity' => $item->quantity,
            'unit_price_snapshot' => $item->unitPriceSnapshot,
            'line_total' => $item->lineTotal,
            'girl_user_id' => $item->girlUserId,
            'girl_amount_snapshot' => $item->girlAmountSnapshot,
            'house_amount_snapshot' => $item->houseAmountSnapshot,
            'waiter_commission_percent_snapshot' => $item->waiterCommissionPercentSnapshot,
            'waiter_commission_amount_snapshot' => $item->waiterCommissionAmountSnapshot,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function payment(SalePayment $payment): array
    {
        return [
            'id' => $payment->id,
            'payment_method' => $payment->paymentMethod,
            'amount' => $payment->amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function saleSummary(Sale $sale): array
    {
        $data = self::sale($sale);
        unset($data['items']);

        return $data;
    }
}
