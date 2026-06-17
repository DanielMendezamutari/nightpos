<?php

declare(strict_types=1);

namespace App\Application\Sale\UseCases;

use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Order\Services\OrderItemPricing;
use App\Application\Sale\DTOs\DirectSaleInput;
use App\Application\Sale\Support\SaleMapper;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashMovementType;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Domain\Sale\Exceptions\SaleDomainException;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Domain\Sale\ValueObjects\PaymentMethod;
use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class CreateDirectSaleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly SaleRepositoryInterface $sales,
        private readonly OrderItemPricing $itemPricing,
        private readonly ProductRepositoryInterface $products,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly PaymentMethodRepositoryInterface $paymentMethods,
        private readonly AuditLogRecorder $audit,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof DirectSaleInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $cashierId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $cashierId === null) {
            throw SaleDomainException::cashSessionRequired();
        }

        if (! $this->staffContext->hasPermission('sales.direct_create')) {
            throw \App\Domain\Auth\Exceptions\PermissionDeniedException::forPermission('sales.direct_create');
        }

        if ($input->items === []) {
            throw SaleDomainException::directSaleItemsRequired();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $cashierId);

        $cashSession = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $cashierId);

        if ($cashSession === null) {
            throw SaleDomainException::cashSessionRequired();
        }

        // Resolve prices and build sale items
        $saleItems = [];
        $computedSubtotal = 0.0;
        $currency = 'BOB';

        foreach ($input->items as $row) {
            $productId = (int) $row['product_id'];
            $saleMode = SaleMode::fromString($row['sale_mode']);
            $quantity = max(1, (int) $row['quantity']);
            $girlUserId = isset($row['girl_user_id']) ? (int) $row['girl_user_id'] : null;

            $product = $this->products->findById($productId, $tenant->id);

            if ($product !== null && $product->requiresAllocation) {
                throw ProductDomainException::directSaleAllocationNotSupported();
            }

            if ($saleMode->isConAcompanante() && $girlUserId === null) {
                throw SaleDomainException::directSaleGirlRequired();
            }

            $pricing = $this->itemPricing->resolve(
                tenantId: $tenant->id,
                branchId: $branch->id,
                productId: $productId,
                saleMode: $saleMode->value,
                quantity: $quantity,
            );

            $computedSubtotal += (float) $pricing['line_total'];
            $currency = $pricing['currency'];

            $saleItems[] = [
                'order_item_id' => null,
                'product_id' => $productId,
                'product_name_snapshot' => $pricing['product_name'],
                'sale_mode' => $saleMode->value,
                'quantity' => $quantity,
                'unit_price_snapshot' => $pricing['unit_price'],
                'line_total' => $pricing['line_total'],
                'girl_user_id' => $girlUserId,
                'girl_amount_snapshot' => $pricing['girl_amount'],
                'house_amount_snapshot' => $pricing['house_amount'],
                'waiter_commission_percent_snapshot' => null,
                'waiter_commission_amount_snapshot' => null,
            ];
        }

        $subtotal = number_format($computedSubtotal, 2, '.', '');
        $total = $subtotal;

        // Validate payments
        $paymentRows = [];
        $paymentSum = 0.0;
        $allowedMethods = $this->paymentMethods->enabledLegacyCodes($tenant->id, $branch->id);

        if ($allowedMethods === []) {
            $allowedMethods = ['CASH', 'QR', 'CARD'];
        }

        foreach ($input->payments as $row) {
            $methodCode = strtoupper((string) $row['method']);

            if (! in_array($methodCode, $allowedMethods, true)) {
                throw SaleDomainException::invalidPaymentMethod($methodCode);
            }

            $method = PaymentMethod::fromString($methodCode);
            $amount = (float) $row['amount'];

            if ($amount <= 0) {
                throw SaleDomainException::invalidPaymentAmount();
            }

            $paymentSum += $amount;
            $paymentRows[] = [
                'payment_method' => $method->value,
                'amount' => number_format($amount, 2, '.', ''),
            ];
        }

        if (abs($paymentSum - $computedSubtotal) > 0.01) {
            throw SaleDomainException::paymentMismatch();
        }

        $paymentMode = count($paymentRows) === 1
            ? $paymentRows[0]['payment_method']
            : 'MIXED';

        $sale = DB::transaction(function () use (
            $tenant,
            $branch,
            $shift,
            $cashSession,
            $cashierId,
            $paymentMode,
            $saleItems,
            $paymentRows,
            $subtotal,
            $total,
            $currency,
        ) {
            $sale = $this->sales->create(
                tenantId: $tenant->id,
                branchId: $branch->id,
                officialShiftId: $shift->id,
                cashSessionId: $cashSession->id,
                orderId: null,
                saleNumber: $this->sales->nextSaleNumber($branch->id),
                cashierUserId: $cashierId,
                waiterUserId: null,
                subtotal: $subtotal,
                total: $total,
                currency: $currency,
                paymentMode: $paymentMode,
                items: $saleItems,
                payments: $paymentRows,
            );

            foreach ($paymentRows as $payment) {
                $this->cashSessions->addMovement(
                    tenantId: $tenant->id,
                    branchId: $branch->id,
                    cashSessionId: $cashSession->id,
                    movementType: CashMovementType::INCOME,
                    amount: $payment['amount'],
                    description: sprintf('Venta directa %s', $sale->saleNumber),
                    paymentMethod: $payment['payment_method'],
                    createdByUserId: $cashierId,
                    sourceType: 'sale',
                    sourceId: $sale->id,
                );
            }

            return $sale;
        });

        $this->audit->record(
            'sale.direct_created',
            'sale',
            $sale->id,
            [
                'total' => $sale->total,
                'payment_mode' => $paymentMode,
                'items_count' => count($saleItems),
            ],
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'direct_sale.created',
            [
                'entity'  => ['type' => 'sale', 'id' => $sale->id],
                'summary' => 'Venta directa registrada',
                'refresh' => ['cash', 'shift_console'],
            ]
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'sale.created',
            [
                'entity'  => ['type' => 'sale', 'id' => $sale->id],
                'summary' => 'Venta registrada',
                'refresh' => ['cash', 'shift_console'],
            ]
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'cash.movement.created',
            [
                'entity'  => ['type' => 'sale', 'id' => $sale->id],
                'summary' => 'Ingreso en caja por venta directa',
                'refresh' => ['cash'],
            ]
        );

        return OperationResult::ok('Venta directa registrada correctamente.', [
            'sale' => SaleMapper::sale($sale),
        ]);
    }
}
