<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\UpdateOrderHeaderInput;
use App\Application\Order\Services\OrderAccessGuard;
use App\Application\Order\Services\OrderPresentationService;
use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateOrderHeaderUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderAccessGuard $accessGuard,
        private readonly ServiceAreaRepositoryInterface $serviceAreas,
        private readonly AuditLogRecorder $audit,
        private readonly OperationalEventEmitter $eventEmitter,
        private readonly OrderPresentationService $presentation,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdateOrderHeaderInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw OrderDomainException::branchRequired();
        }

        $order = $this->accessGuard->loadOrder($input->orderId);
        $status = $this->accessGuard->assertNotTerminal($order);

        if ($status->value !== OrderStatus::OPEN) {
            throw OrderDomainException::notModifiable();
        }

        $tableLabel = $input->tableLabel !== null ? trim($input->tableLabel) : $order->tableLabel;
        $serviceAreaId = $input->clearServiceArea
            ? null
            : ($input->serviceAreaId ?? $order->serviceAreaId);
        $notes = $input->notes !== null ? trim($input->notes) : $order->notes;

        if ($serviceAreaId !== null && $serviceAreaId > 0) {
            $area = $this->serviceAreas->findById($serviceAreaId, $tenant->id, $branch->id);

            if ($area === null || $area['status'] !== 'active') {
                throw OrderDomainException::invalidTableLabel();
            }

            if ($tableLabel === null || $tableLabel === '') {
                $tableLabel = $area['name'];
            }
        }

        if ($tableLabel === null || $tableLabel === '') {
            throw OrderDomainException::invalidTableLabel();
        }

        $updated = $this->orders->updateHeader(
            tenantId: $tenant->id,
            orderId: $order->id,
            tableLabel: $tableLabel,
            serviceAreaId: $serviceAreaId > 0 ? $serviceAreaId : null,
            notes: $notes !== '' ? $notes : null,
        );

        $this->audit->record('order.header_updated', 'order', $order->id, [
            'table_label' => $tableLabel,
            'service_area_id' => $serviceAreaId,
        ]);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.updated',
            OrderOperationalEventPayload::build(
                orderId: $order->id,
                status: $updated->status,
                source: 'update_order_header',
                summary: 'Comanda actualizada',
            )
        );

        return OperationResult::ok('Comanda actualizada.', [
            'order' => $this->presentation->presentOrder($updated, $tenant->id),
        ]);
    }
}
