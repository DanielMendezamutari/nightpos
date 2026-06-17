<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\SyncOrderItemAllocationsInput;
use App\Application\Order\Services\BraceletAllocationValidator;
use App\Application\Order\Services\OrderItemPricing;
use App\Application\Order\Services\OrderPresentationService;
use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderItemAllocationRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\AllocationType;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class SyncOrderItemAllocationsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly OrderItemAllocationRepositoryInterface $allocations,
        private readonly OrderItemPricing $itemPricing,
        private readonly BraceletAllocationValidator $allocationValidator,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
        private readonly OrderPresentationService $presentation,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof SyncOrderItemAllocationsInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw OrderDomainException::branchRequired();
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

        $item = collect($order->items)->firstWhere('id', $input->itemId);

        if ($item === null) {
            throw OrderDomainException::itemNotFound();
        }

        if ($item->isCancelled()) {
            throw OrderDomainException::itemAlreadyCancelled();
        }

        $product = $this->products->findById($item->productId, $tenant->id);

        if ($product === null || ! $product->requiresAllocation) {
            throw OrderDomainException::allocationNotAllowed();
        }

        $pricing = $this->itemPricing->resolve(
            tenantId: $tenant->id,
            branchId: $branch->id,
            productId: $item->productId,
            saleMode: $item->saleMode,
            quantity: $item->quantity,
        );

        $rows = $this->allocationValidator->validateAndBuildRows(
            tenantId: $tenant->id,
            product: $product,
            quantity: $item->quantity,
            girlAmountPerCombo: $pricing['girl_amount_per_combo'],
            draftRows: $input->allocations,
        );

        $this->allocations->sync(
            tenantId: $tenant->id,
            branchId: $branch->id,
            orderItemId: $item->id,
            allocationType: $product->allocationType ?? AllocationType::GIRL_BRACELET_UNITS,
            rows: $rows,
        );

        $updated = $this->orders->findById($order->id, $tenant->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: $updated?->status ?? $order->status,
                source: 'sync_order_item_allocations',
                summary: 'Reparto de manillas actualizado',
            )
        );

        return OperationResult::ok('Reparto de manillas guardado.', [
            'order' => $this->presentation->presentOrder($updated ?? $order, $tenant->id),
        ]);
    }
}
