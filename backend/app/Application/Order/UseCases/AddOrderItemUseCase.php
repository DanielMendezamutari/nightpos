<?php



declare(strict_types=1);



namespace App\Application\Order\UseCases;



use App\Application\Order\DTOs\AddOrderItemInput;

use App\Application\Order\Services\OrderItemPricing;

use App\Application\Order\Services\OrderPresentationService;

use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;

use App\Application\Waiter\Services\WaiterOrderAccessPolicy;

use App\Domain\Order\Exceptions\OrderDomainException;

use App\Domain\Order\Exceptions\OrderNotFoundException;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

use App\Domain\Order\ValueObjects\OrderStatus;

use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Domain\Product\ValueObjects\SaleMode;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class AddOrderItemUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly OrderRepositoryInterface $orders,

        private readonly ProductRepositoryInterface $products,

        private readonly OrderItemPricing $itemPricing,

        private readonly WaiterOrderAccessPolicy $waiterAccess,

        private readonly OrderPresentationService $presentation,

        private readonly OperationalEventEmitter $eventEmitter,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof AddOrderItemInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();



        if ($tenant === null || $branch === null) {

            throw OrderDomainException::branchRequired();

        }



        if ($input->quantity < 1) {

            throw OrderDomainException::invalidQuantity();

        }



        $order = $this->orders->findById($input->orderId, $tenant->id);



        if ($order === null) {

            throw new OrderNotFoundException();

        }



        $this->waiterAccess->assertCanAccess($order);



        $status = OrderStatus::fromString($order->status);



        if (! $status->allowsItemChanges()) {

            throw OrderDomainException::notModifiable();

        }



        $product = $this->products->findById($input->productId, $tenant->id);



        if ($product === null) {

            throw ProductDomainException::notFound();

        }



        $saleMode = SaleMode::fromString($input->saleMode);



        $girlUserId = $input->girlUserId;



        if ($product->requiresAllocation) {

            if ($girlUserId !== null) {

                throw OrderDomainException::girlNotAllowedWithAllocation();

            }



            $girlUserId = null;

        }



        $pricing = $this->itemPricing->resolve(

            tenantId: $tenant->id,

            branchId: $branch->id,

            productId: $input->productId,

            saleMode: $saleMode->value,

            quantity: $input->quantity,

        );



        $this->orders->addItem(

            tenantId: $tenant->id,

            branchId: $branch->id,

            orderId: $order->id,

            productId: $input->productId,

            productName: $pricing['product_name'],

            saleMode: $saleMode->value,

            quantity: $input->quantity,

            unitPrice: $pricing['unit_price'],

            lineTotal: $pricing['line_total'],

            girlAmount: $pricing['girl_amount'],

            houseAmount: $pricing['house_amount'],

            girlUserId: $girlUserId,

            notes: $input->notes,

        );



        $this->orders->recalculateTotals($order->id);

        $updated = $this->orders->findById($order->id, $tenant->id);



        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: $updated?->status ?? $order->status,
                source: 'add_order_item',
                summary: 'Comanda actualizada: ' . $order->tableLabel,
            )
        );



        return OperationResult::ok('Producto agregado a la comanda.', [

            'order' => $this->presentation->presentOrder($updated ?? $order, $tenant->id),

        ]);

    }

}


