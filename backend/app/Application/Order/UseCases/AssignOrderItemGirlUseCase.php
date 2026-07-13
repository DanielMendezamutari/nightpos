<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\AssignOrderItemGirlInput;
use App\Application\Order\Services\OrderPresentationService;
use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class AssignOrderItemGirlUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductRepositoryInterface $products,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
        private readonly OrderPresentationService $presentation,
        private readonly AuditLogRecorder $audit,
        private readonly OperationalEventEmitter $eventEmitter,
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

        $status = strtoupper(trim($order->status));

        if (! in_array($status, ['OPEN', 'SENT_TO_BAR'], true)) {
            throw OrderDomainException::notModifiable();
        }

        $item = collect($order->items)->firstWhere('id', $input->itemId);

        if ($item === null) {
            throw new OrderNotFoundException();
        }

        if (! SaleMode::fromString($item->saleMode)->isConAcompanante()) {
            throw OrderDomainException::notModifiable();
        }

        if ($item->itemStatus === 'CANCELLED') {
            throw OrderDomainException::itemAlreadyCancelled();
        }

        $product = $this->products->findById($item->productId, $tenant->id);

        if ($product !== null && $product->requiresAllocation) {
            throw OrderDomainException::girlNotAllowedWithAllocation();
        }

        $currentGirlUserId = $item->girlUserId;
        $newGirlUserId = $input->girlUserId;
        $isChange = $currentGirlUserId !== null && $currentGirlUserId !== $newGirlUserId;

        if ($isChange && trim((string) $input->reason) === '') {
            throw OrderDomainException::changeReasonRequired();
        }

        $previousGirl = $this->resolveGirl($tenant->id, $currentGirlUserId);
        $newGirl = $this->resolveGirl($tenant->id, $newGirlUserId);

        $this->orders->updateItemGirlUserId($tenant->id, $order->id, $input->itemId, $newGirlUserId);

        $updated = $this->orders->findById($order->id, $tenant->id);

        $auditAction = $isChange ? 'order.item_girl_changed' : 'order.item_girl_assigned';

        $this->audit->record($auditAction, 'order', $order->id, [
            'order_item_id' => $item->id,
            'previous_girl_user_id' => $currentGirlUserId,
            'previous_girl_name' => $previousGirl?->name,
            'new_girl_user_id' => $newGirlUserId,
            'new_girl_name' => $newGirl?->name,
            'reason' => $input->reason,
            'changed_by_user_id' => $this->waiterAccess->waiterUserId(),
            'order_status' => $status,
            'item_status' => $item->itemStatus,
        ]);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            $auditAction,
            [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'previous_girl_user_id' => $currentGirlUserId,
                'previous_girl_name' => $previousGirl?->name,
                'new_girl_user_id' => $newGirlUserId,
                'new_girl_name' => $newGirl?->name,
                'reason' => $input->reason,
                'source' => 'assign_order_item_girl',
            ]
        );

        $summary = $isChange
            ? sprintf('Chica cambiada de %s a %s.', $previousGirl?->name ?? 'sin asignar', $newGirl?->name ?? 'sin asignar')
            : sprintf('Chica asignada a %s.', $newGirl?->name ?? 'sin asignar');

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: $updated?->status ?? $order->status,
                source: 'assign_order_item_girl',
                summary: $summary,
            )
        );

        return OperationResult::ok($isChange ? 'Chica cambiada.' : 'Chica asignada.', [
            'order' => $this->presentation->presentOrder($updated ?? $order, $tenant->id),
        ]);
    }

    private function resolveGirl(int $tenantId, ?int $girlUserId): ?UserModel
    {
        if ($girlUserId === null) {
            return null;
        }

        return UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $girlUserId)
            ->first();
    }
}


