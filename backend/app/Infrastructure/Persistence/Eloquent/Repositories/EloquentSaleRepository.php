<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Sale\Entities\Sale;
use App\Domain\Sale\Entities\SaleItem;
use App\Domain\Sale\Entities\SalePayment;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Domain\Sale\Exceptions\SaleNotFoundException;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalePaymentModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use Illuminate\Support\Carbon;

final class EloquentSaleRepository implements SaleRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Sale
    {
        $model = SaleModel::query()
            ->with(['items', 'payments'])
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->mapSale($model) : null;
    }

    public function existsForOrder(int $orderId): bool
    {
        return SaleModel::query()->where('order_id', $orderId)->exists();
    }

    public function listForBranch(int $tenantId, int $branchId, ?int $cashSessionId = null, ?int $officialShiftId = null): array
    {
        $query = SaleModel::query()
            ->with(['items', 'payments'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id');

        if ($cashSessionId !== null) {
            $query->where('cash_session_id', $cashSessionId);
        }

        if ($officialShiftId !== null) {
            $query->where('official_shift_id', $officialShiftId);
        }

        return $query->get()
            ->map(fn (SaleModel $model) => $this->mapSale($model))
            ->all();
    }

    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        int $cashSessionId,
        ?int $orderId,
        string $saleNumber,
        int $cashierUserId,
        ?int $waiterUserId,
        string $subtotal,
        string $total,
        string $currency,
        string $paymentMode,
        array $items,
        array $payments,
    ): Sale {
        $model = SaleModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $officialShiftId,
            'cash_session_id' => $cashSessionId,
            'order_id' => $orderId,
            'sale_number' => $saleNumber,
            'cashier_user_id' => $cashierUserId,
            'waiter_user_id' => $waiterUserId,
            'subtotal' => $subtotal,
            'total' => $total,
            'currency' => $currency,
            'payment_mode' => $paymentMode,
            'status' => 'PAID',
            'paid_at' => Carbon::now(),
        ]);

        foreach ($items as $row) {
            SaleItemModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'sale_id' => $model->id,
                'order_item_id' => $row['order_item_id'],
                'product_id' => $row['product_id'],
                'product_name_snapshot' => $row['product_name_snapshot'],
                'sale_mode' => $row['sale_mode'],
                'quantity' => $row['quantity'],
                'unit_price_snapshot' => $row['unit_price_snapshot'],
                'line_total' => $row['line_total'],
                'girl_user_id' => $row['girl_user_id'],
                'girl_amount_snapshot' => $row['girl_amount_snapshot'],
                'house_amount_snapshot' => $row['house_amount_snapshot'],
                'waiter_commission_percent_snapshot' => $row['waiter_commission_percent_snapshot'],
                'waiter_commission_amount_snapshot' => $row['waiter_commission_amount_snapshot'],
            ]);
        }

        foreach ($payments as $row) {
            SalePaymentModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'sale_id' => $model->id,
                'payment_method' => $row['payment_method'],
                'amount' => $row['amount'],
            ]);
        }

        return $this->mapSale($model->fresh()->load(['items', 'payments']));
    }

    public function nextSaleNumber(int $branchId): string
    {
        $last = SaleModel::query()
            ->where('branch_id', $branchId)
            ->orderByDesc('id')
            ->value('sale_number');

        if ($last === null) {
            return 'V-0001';
        }

        $number = (int) preg_replace('/\D/', '', $last);

        return 'V-'.str_pad((string) ($number + 1), 4, '0', STR_PAD_LEFT);
    }

    public function sumPaymentsByMethodForSession(int $cashSessionId): array
    {
        $rows = SalePaymentModel::query()
            ->whereIn('sale_id', SaleModel::query()->where('cash_session_id', $cashSessionId)->select('id'))
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $result = ['cash' => '0.00', 'qr' => '0.00', 'card' => '0.00'];

        foreach ($rows as $row) {
            $key = strtolower($row->payment_method);
            if (isset($result[$key])) {
                $result[$key] = number_format((float) $row->total, 2, '.', '');
            }
        }

        return $result;
    }

    public function getSalesSummary(CashSessionId $cashSessionId): array
    {
        $sessionId = $cashSessionId->value;

        $sales = SaleModel::query()
            ->where('cash_session_id', $sessionId)
            ->where('status', 'PAID')
            ->get(['id', 'order_id', 'total', 'payment_mode']);

        $roomServices = RoomServiceModel::query()
            ->where('cash_session_id', $sessionId)
            ->get(['id', 'total_amount', 'payment_method']);

        $bracelets = BraceletModel::query()
            ->where('cash_session_id', $sessionId)
            ->get(['id', 'total_amount', 'payment_method']);

        $sourceBuckets = [
            'order_sales' => ['count' => 0, 'amount' => 0.0],
            'direct_sales' => ['count' => 0, 'amount' => 0.0],
            'room_services' => ['count' => 0, 'amount' => 0.0],
            'bracelets' => ['count' => 0, 'amount' => 0.0],
            'other_sales' => ['count' => 0, 'amount' => 0.0],
        ];

        $methodBuckets = [
            'CASH' => ['count' => 0, 'amount' => 0.0],
            'QR' => ['count' => 0, 'amount' => 0.0],
            'CARD' => ['count' => 0, 'amount' => 0.0],
            'MIXED' => ['count' => 0, 'amount' => 0.0],
        ];

        $totalSales = 0.0;
        $salesCount = 0;
        $mixedSalesCount = 0;

        foreach ($sales as $sale) {
            $amount = (float) $sale->total;
            $totalSales += $amount;
            $salesCount++;

            $sourceKey = $sale->order_id !== null ? 'order_sales' : 'direct_sales';
            $sourceBuckets[$sourceKey]['count']++;
            $sourceBuckets[$sourceKey]['amount'] += $amount;

            $bucket = $this->resolveSalesMethodBucket((string) $sale->payment_mode);
            $methodBuckets[$bucket]['count']++;
            $methodBuckets[$bucket]['amount'] += $amount;

            if ($bucket === 'MIXED') {
                $mixedSalesCount++;
            }
        }

        foreach ($roomServices as $roomService) {
            $amount = (float) $roomService->total_amount;
            $totalSales += $amount;
            $salesCount++;
            $sourceBuckets['room_services']['count']++;
            $sourceBuckets['room_services']['amount'] += $amount;

            $bucket = $this->resolveSalesMethodBucket((string) $roomService->payment_method);
            $methodBuckets[$bucket]['count']++;
            $methodBuckets[$bucket]['amount'] += $amount;
        }

        foreach ($bracelets as $bracelet) {
            $amount = (float) $bracelet->total_amount;
            $totalSales += $amount;
            $salesCount++;
            $sourceBuckets['bracelets']['count']++;
            $sourceBuckets['bracelets']['amount'] += $amount;

            $bucket = $this->resolveSalesMethodBucket((string) $bracelet->payment_method);
            $methodBuckets[$bucket]['count']++;
            $methodBuckets[$bucket]['amount'] += $amount;
        }

        $itemCounts = SaleItemModel::query()
            ->whereIn('sale_id', SaleModel::query()->where('cash_session_id', $sessionId)->select('id'))
            ->selectRaw("SUM(CASE WHEN sale_mode = 'PRODUCT' THEN quantity ELSE 0 END) as products_sold_count")
            ->selectRaw("SUM(CASE WHEN sale_mode = 'SERVICE' THEN quantity ELSE 0 END) as services_sold_count")
            ->first();

        $bySource = [
            'order_sales' => number_format($sourceBuckets['order_sales']['amount'], 2, '.', ''),
            'direct_sales' => number_format($sourceBuckets['direct_sales']['amount'], 2, '.', ''),
            'room_services' => number_format($sourceBuckets['room_services']['amount'], 2, '.', ''),
            'bracelets' => number_format($sourceBuckets['bracelets']['amount'], 2, '.', ''),
            'other_sales' => number_format($sourceBuckets['other_sales']['amount'], 2, '.', ''),
        ];

        $byMethod = [
            'CASH' => number_format($methodBuckets['CASH']['amount'], 2, '.', ''),
            'QR' => number_format($methodBuckets['QR']['amount'], 2, '.', ''),
            'CARD' => number_format($methodBuckets['CARD']['amount'], 2, '.', ''),
            'MIXED' => number_format($methodBuckets['MIXED']['amount'], 2, '.', ''),
        ];

        return [
            'total_sales_amount' => number_format($totalSales, 2, '.', ''),
            'total_sales' => number_format($totalSales, 2, '.', ''),
            'sales_count' => $salesCount,
            'cash_total' => $byMethod['CASH'],
            'qr_total' => $byMethod['QR'],
            'card_total' => $byMethod['CARD'],
            'mixed_total' => $byMethod['MIXED'],
            'sales_cash' => $byMethod['CASH'],
            'sales_qr' => $byMethod['QR'],
            'sales_card' => $byMethod['CARD'],
            'mixed_sales_count' => $mixedSalesCount,
            'by_method' => [
                'cash' => $byMethod['CASH'],
                'qr' => $byMethod['QR'],
                'card' => $byMethod['CARD'],
                'mixed' => $byMethod['MIXED'],
            ],
            'sales_by_method' => [
                'cash' => $byMethod['CASH'],
                'qr' => $byMethod['QR'],
                'card' => $byMethod['CARD'],
            ],
            'by_source' => $bySource,
            'sales_by_source' => $bySource,
            'products_sold_count' => $itemCounts->products_sold_count ?? 0,
            'services_sold_count' => $itemCounts->services_sold_count ?? 0,
        ];
    }

    private function resolveSalesMethodBucket(string $method): string
    {
        return match (strtoupper(trim($method))) {
            'QR' => 'QR',
            'CARD' => 'CARD',
            'MIXED' => 'MIXED',
            default => 'CASH',
        };
    }

    private function mapSale(SaleModel $model): Sale
    {
        $items = $model->relationLoaded('items')
            ? $model->items->map(fn (SaleItemModel $item) => new SaleItem(
                id: (int) $item->id,
                productId: (int) $item->product_id,
                productNameSnapshot: $item->product_name_snapshot,
                saleMode: $item->sale_mode,
                quantity: (int) $item->quantity,
                unitPriceSnapshot: (string) $item->unit_price_snapshot,
                lineTotal: (string) $item->line_total,
                girlUserId: $item->girl_user_id !== null ? (int) $item->girl_user_id : null,
                girlAmountSnapshot: $item->girl_amount_snapshot !== null ? (string) $item->girl_amount_snapshot : null,
                houseAmountSnapshot: $item->house_amount_snapshot !== null ? (string) $item->house_amount_snapshot : null,
                waiterCommissionPercentSnapshot: $item->waiter_commission_percent_snapshot !== null
                    ? (string) $item->waiter_commission_percent_snapshot
                    : null,
                waiterCommissionAmountSnapshot: $item->waiter_commission_amount_snapshot !== null
                    ? (string) $item->waiter_commission_amount_snapshot
                    : null,
            ))->all()
            : [];

        $payments = $model->relationLoaded('payments')
            ? $model->payments->map(fn (SalePaymentModel $p) => new SalePayment(
                id: (int) $p->id,
                paymentMethod: $p->payment_method,
                amount: (string) $p->amount,
            ))->all()
            : [];

        return new Sale(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: (int) $model->branch_id,
            officialShiftId: (int) $model->official_shift_id,
            cashSessionId: (int) $model->cash_session_id,
            orderId: $model->order_id !== null ? (int) $model->order_id : null,
            saleNumber: $model->sale_number,
            cashierUserId: (int) $model->cashier_user_id,
            waiterUserId: $model->waiter_user_id !== null ? (int) $model->waiter_user_id : null,
            subtotal: (string) $model->subtotal,
            total: (string) $model->total,
            currency: $model->currency,
            paymentMode: $model->payment_mode,
            status: $model->status,
            paidAt: $model->paid_at?->toIso8601String() ?? '',
            items: $items,
            payments: $payments,
        );
    }
}
