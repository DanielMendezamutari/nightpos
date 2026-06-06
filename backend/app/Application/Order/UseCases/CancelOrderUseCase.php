<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\OrderActionInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Order\Services\OrderAccessGuard;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CancelOrderUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderAccessGuard $accessGuard,
        private readonly AuditLogRecorder $audit,
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

        if (! $this->staffContext->hasPermission('orders.cancel')) {
            throw PermissionDeniedException::forPermission('orders.cancel');
        }

        $order = $this->accessGuard->loadOrder($input->orderId);
        $status = OrderStatus::fromString($order->status);

        if (! $status->canCancel()) {
            throw OrderDomainException::notModifiable();
        }

        $updated = $this->orders->updateStatus(
            orderId: $order->id,
            tenantId: $tenant->id,
            status: OrderStatus::CANCELLED,
            changedByUserId: $this->staffContext->userId(),
        );

        $this->audit->record('order.cancelled', 'order', $order->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.cancelled',
            [
                'entity'  => ['type' => 'order', 'id' => $order->id],
                'summary' => 'Comanda cancelada: ' . $order->tableLabel,
                'refresh' => ['orders'],
            ]
        );

        return OperationResult::ok('Comanda cancelada.', [
            'order' => OrderMapper::order($updated, false),
        ]);
    }
}
