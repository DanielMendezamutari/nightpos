<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\AssignOrderItemGirlInput;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class AssignOrderItemGirlUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof AssignOrderItemGirlInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw OrderDomainException::branchRequired();
        }

        $order = $this->orders->findById($input->orderId, $tenant->id);

        if ($order === null || $order->branchId !== $branch->id) {
            throw new OrderNotFoundException();
        }

        $this->waiterAccess->assertCanAccess($order);

        $status = OrderStatus::fromString($order->status);

        if (! $status->allowsItemChanges()) {
            throw OrderDomainException::notModifiable();
        }

        $item = collect($order->items)->firstWhere('id', $input->itemId);

        if ($item === null) {
            throw new OrderNotFoundException();
        }

        if (! SaleMode::fromString($item->saleMode)->isConAcompanante()) {
            throw OrderDomainException::notModifiable();
        }

        $this->orders->updateItemGirlUserId($tenant->id, $order->id, $input->itemId, $input->girlUserId);

        $updated = $this->orders->findById($order->id, $tenant->id);

        return OperationResult::ok('Chica asignada al ítem.', [
            'order' => OrderMapper::order($updated),
        ]);
    }
}
