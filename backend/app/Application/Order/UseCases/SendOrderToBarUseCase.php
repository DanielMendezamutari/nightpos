<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\OrderActionInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class SendOrderToBarUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof OrderActionInput) {
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

        if ($order->branchId !== $branch->id) {
            throw new OrderNotFoundException();
        }

        $this->waiterAccess->assertCanAccess($order);

        $status = OrderStatus::fromString($order->status);

        if (! $status->canSendToBar()) {
            throw OrderDomainException::cannotSendToBar();
        }

        $activeItems = array_values(array_filter(
            $order->items,
            static fn ($item) => $item->itemStatus !== 'CANCELLED',
        ));

        if ($activeItems === []) {
            throw OrderDomainException::cannotSendToBar();
        }

        foreach ($activeItems as $item) {
            if (SaleMode::fromString($item->saleMode)->isConAcompanante() && $item->girlUserId === null) {
                throw OrderDomainException::girlRequiredForSaleMode();
            }
        }

        $this->orders->markItemsSentToBar($order->id);

        $this->orders->updateStatus(
            orderId: $order->id,
            tenantId: $tenant->id,
            status: OrderStatus::SENT_TO_BAR,
            changedByUserId: $this->staffContext->userId(),
        );

        $updated = $this->orders->findById($order->id, $tenant->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.sent_to_bar',
            [
                'entity'  => ['type' => 'order', 'id' => $order->id],
                'summary' => 'Comanda enviada a barra: ' . $order->tableLabel,
                'refresh' => ['orders'],
            ]
        );

        return OperationResult::ok('Comanda enviada a barra.', [
            'order' => OrderMapper::order($updated ?? $order),
        ]);
    }
}
