<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\CancelOrderItemInput;
use App\Application\Order\Services\OrderAccessGuard;
use App\Application\Order\Services\OrderPresentationService;
use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CancelOrderItemUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderAccessGuard $accessGuard,
        private readonly AuditLogRecorder $audit,
        private readonly OperationalEventEmitter $eventEmitter,
        private readonly OrderPresentationService $presentation,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CancelOrderItemInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw OrderDomainException::branchRequired();
        }

        $reason = trim($input->reason);

        if ($reason === '') {
            throw OrderDomainException::cancelReasonRequired();
        }

        $order = $this->accessGuard->loadOrder($input->orderId);
        $status = $this->accessGuard->assertNotTerminal($order);
        $this->accessGuard->assertAllowsLineChanges($status);

        if ($status->value !== OrderStatus::SENT_TO_BAR) {
            throw OrderDomainException::itemNotRemovable();
        }

        $item = collect($order->items)->firstWhere('id', $input->itemId);

        if ($item === null) {
            throw OrderDomainException::itemNotFound();
        }

        if ($item->isCancelled()) {
            throw OrderDomainException::itemAlreadyCancelled();
        }

        if (! $item->isSent()) {
            throw OrderDomainException::itemNotRemovable();
        }

        $this->orders->cancelItem(
            tenantId: $tenant->id,
            orderId: $order->id,
            itemId: $item->id,
            reason: $reason,
            cancelledByUserId: $userId,
        );

        $updated = $this->orders->recalculateTotals($order->id);

        $this->audit->record('order.item_cancelled', 'order', $order->id, [
            'item_id' => $item->id,
            'reason' => $reason,
        ]);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: $updated?->status ?? $order->status,
                source: 'cancel_order_item',
                summary: 'Línea cancelada',
            )
        );

        return OperationResult::ok('Línea cancelada.', [
            'order' => $this->presentation->presentOrder($updated, $tenant->id),
        ]);
    }
}
