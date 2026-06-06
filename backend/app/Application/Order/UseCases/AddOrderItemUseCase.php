<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\AddOrderItemInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Services\ProductPriceResolver;
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
        private readonly ProductPriceResolver $priceResolver,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
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

        $saleMode = SaleMode::fromString($input->saleMode);

        $productPrice = $this->priceResolver->resolve(
            tenantId: $tenant->id,
            productId: $input->productId,
            saleMode: $saleMode->value,
            branchId: $branch->id,
        );

        $product = $this->products->findById($input->productId, $tenant->id);
        $productName = $product?->name ?? 'Producto';

        $unitPrice = (float) $productPrice->price;
        $lineTotal = round($unitPrice * $input->quantity, 2);

        $this->orders->addItem(
            tenantId: $tenant->id,
            branchId: $branch->id,
            orderId: $order->id,
            productId: $input->productId,
            productName: $productName,
            saleMode: $saleMode->value,
            quantity: $input->quantity,
            unitPrice: (string) $unitPrice,
            lineTotal: (string) $lineTotal,
            girlAmount: $productPrice->girlAmount,
            houseAmount: $productPrice->houseAmount,
            girlUserId: $input->girlUserId,
            notes: $input->notes,
        );

        $updated = $this->orders->recalculateTotals($order->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            [
                'entity'  => ['type' => 'order', 'id' => $order->id],
                'summary' => 'Comanda actualizada: ' . $order->tableLabel,
                'refresh' => ['orders'],
            ]
        );

        return OperationResult::ok('Producto agregado a la comanda.', [
            'order' => OrderMapper::order($updated),
        ]);
    }
}
