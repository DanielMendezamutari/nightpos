<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\RemoveOrderItemInput;
use App\Application\Order\Services\OrderAccessGuard;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class RemoveOrderItemUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderAccessGuard $accessGuard,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof RemoveOrderItemInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw OrderDomainException::branchRequired();
        }

        $order = $this->accessGuard->loadOrder($input->orderId);
        $status = $this->accessGuard->assertNotTerminal($order);
        $this->accessGuard->assertAllowsLineChanges($status);

        if ($status->value !== OrderStatus::OPEN) {
            throw OrderDomainException::itemNotRemovable();
        }

        $item = collect($order->items)->firstWhere('id', $input->itemId);

        if ($item === null) {
            throw OrderDomainException::itemNotFound();
        }

        if (! $item->isPending()) {
            throw OrderDomainException::itemNotRemovable();
        }

        $this->orders->removeItem($tenant->id, $order->id, $item->id);
        $updated = $this->orders->recalculateTotals($order->id);

        $this->audit->record('order.item_removed', 'order', $order->id, [
            'item_id' => $item->id,
            'product_name' => $item->productName,
        ]);

        return OperationResult::ok('Ítem eliminado de la comanda.', [
            'order' => OrderMapper::order($updated),
        ]);
    }
}
