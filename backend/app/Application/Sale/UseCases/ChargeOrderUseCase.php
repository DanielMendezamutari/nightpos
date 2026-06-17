<?php



declare(strict_types=1);



namespace App\Application\Sale\UseCases;



use App\Application\Cash\Services\OpenCashSessionResolver;

use App\Application\Order\Services\OrderItemReadinessChecker;

use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;

use App\Application\Sale\DTOs\ChargeOrderInput;

use App\Application\Sale\Support\SaleMapper;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;

use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;

use App\Domain\Cash\ValueObjects\CashMovementType;

use App\Domain\Order\Exceptions\OrderNotFoundException;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

use App\Domain\Order\ValueObjects\OrderStatus;

use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Domain\Sale\Exceptions\SaleDomainException;

use App\Domain\Sale\Repositories\SaleItemAllocationRepositoryInterface;

use App\Domain\Sale\Repositories\SaleRepositoryInterface;

use App\Domain\Sale\ValueObjects\PaymentMethod;

use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;

use App\Infrastructure\Services\WaiterCommissionResolver;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Application\Support\AuditLogRecorder;

use App\Shared\Contracts\UseCaseInterface;

use Illuminate\Support\Facades\DB;



final class ChargeOrderUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly OrderRepositoryInterface $orders,

        private readonly ProductRepositoryInterface $products,

        private readonly OpenCashSessionResolver $cashSessionResolver,

        private readonly CashSessionRepositoryInterface $cashSessions,

        private readonly SaleRepositoryInterface $sales,

        private readonly SaleItemAllocationRepositoryInterface $saleAllocations,

        private readonly WaiterCommissionResolver $waiterCommission,

        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,

        private readonly PaymentMethodRepositoryInterface $paymentMethods,

        private readonly OrderItemReadinessChecker $readinessChecker,

        private readonly AuditLogRecorder $audit,

        private readonly OperationalEventEmitter $eventEmitter,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof ChargeOrderInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $cashierId = $this->staffContext->userId();



        if ($tenant === null || $branch === null || $cashierId === null) {

            throw SaleDomainException::cashSessionRequired();

        }



        if (! $this->staffContext->hasPermission('sales.charge')) {

            throw \App\Domain\Auth\Exceptions\PermissionDeniedException::forPermission('sales.charge');

        }



        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $cashierId);



        $cashSession = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $cashierId);



        if ($cashSession === null) {

            throw SaleDomainException::cashSessionRequired();

        }



        $order = $this->orders->findById($input->orderId, $tenant->id);



        if ($order === null || $order->branchId !== $branch->id) {

            throw new OrderNotFoundException();

        }



        if ($order->status === OrderStatus::CANCELLED) {

            throw SaleDomainException::orderNotChargeable();

        }



        if ($order->status === OrderStatus::BILLED) {

            throw SaleDomainException::orderAlreadyBilled();

        }



        if ($this->sales->existsForOrder($order->id)) {

            throw SaleDomainException::orderAlreadyBilled();

        }



        $activeItems = array_values(array_filter(

            $order->items,

            static fn ($item) => $item->itemStatus !== 'CANCELLED',

        ));



        if ($activeItems === []) {

            throw SaleDomainException::orderEmpty();

        }



        $this->readinessChecker->assertOrderReady($tenant->id, $order);



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



        $orderTotal = (float) $order->total;



        if (abs($paymentSum - $orderTotal) > 0.01) {

            throw SaleDomainException::paymentMismatch();

        }



        $paymentMode = count($paymentRows) === 1

            ? $paymentRows[0]['payment_method']

            : 'MIXED';



        $waiterPercent = $this->waiterCommission->resolvePercent($order->waiterUserId, $tenant->id);



        $saleItems = [];



        foreach ($activeItems as $item) {

            $commissionAmount = $this->waiterCommission->calculateAmount($item->lineTotal, $waiterPercent);

            $product = $this->products->findById($item->productId, $tenant->id);

            $requiresAllocation = $product?->requiresAllocation ?? false;



            $saleItems[] = [

                'order_item_id' => $item->id,

                'product_id' => $item->productId,

                'product_name_snapshot' => $item->productName,

                'sale_mode' => $item->saleMode,

                'quantity' => $item->quantity,

                'unit_price_snapshot' => $item->unitPrice,

                'line_total' => $item->lineTotal,

                'girl_user_id' => $requiresAllocation ? null : $item->girlUserId,

                'girl_amount_snapshot' => $item->girlAmount,

                'house_amount_snapshot' => $item->houseAmount,

                'waiter_commission_percent_snapshot' => $waiterPercent,

                'waiter_commission_amount_snapshot' => $commissionAmount,

            ];

        }



        $sale = DB::transaction(function () use (

            $tenant,

            $branch,

            $shift,

            $cashSession,

            $order,

            $cashierId,

            $paymentMode,

            $saleItems,

            $paymentRows,

        ) {

            $sale = $this->sales->create(

                tenantId: $tenant->id,

                branchId: $branch->id,

                officialShiftId: $shift->id,

                cashSessionId: $cashSession->id,

                orderId: $order->id,

                saleNumber: $this->sales->nextSaleNumber($branch->id),

                cashierUserId: $cashierId,

                waiterUserId: $order->waiterUserId,

                subtotal: $order->subtotal,

                total: $order->total,

                currency: $order->currency,

                paymentMode: $paymentMode,

                items: $saleItems,

                payments: $paymentRows,

            );



            $saleItemRows = SaleItemModel::query()

                ->where('sale_id', $sale->id)

                ->get();



            foreach ($saleItemRows as $saleItemRow) {

                if ($saleItemRow->order_item_id === null) {

                    continue;

                }



                $this->saleAllocations->snapshotFromOrderItem(

                    tenantId: $tenant->id,

                    branchId: $branch->id,

                    saleItemId: (int) $saleItemRow->id,

                    orderItemId: (int) $saleItemRow->order_item_id,

                );

            }



            foreach ($paymentRows as $payment) {

                $this->cashSessions->addMovement(

                    tenantId: $tenant->id,

                    branchId: $branch->id,

                    cashSessionId: $cashSession->id,

                    movementType: CashMovementType::INCOME,

                    amount: $payment['amount'],

                    description: sprintf('Cobro comanda %s', $order->orderNumber),

                    paymentMethod: $payment['payment_method'],

                    createdByUserId: $cashierId,

                );

            }



            $this->orders->updateStatus(

                orderId: $order->id,

                tenantId: $tenant->id,

                status: OrderStatus::BILLED,

                changedByUserId: $cashierId,

            );



            return $sale;

        });



        $this->audit->record(

            'sale.charged',

            'sale',

            $sale->id,

            [

                'order_id' => $order->id,

                'total' => $sale->total,

                'payment_mode' => $paymentMode,

            ],

        );



        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.billed',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: OrderStatus::BILLED,
                source: 'charge_order',
                summary: 'Comanda cobrada: ' . $order->tableLabel,
                refresh: ['orders', 'cash'],
            )
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

                'summary' => 'Ingreso en caja por comanda cobrada',

                'refresh' => ['cash'],

            ]

        );



        return OperationResult::ok('Comanda cobrada correctamente.', [

            'sale' => SaleMapper::sale($sale),

            'order_status' => OrderStatus::BILLED,

        ]);

    }

}


