<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\GirlIncome\Services\GirlStaffValidator;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Order\DTOs\CreateOrderInput;
use App\Application\Order\Support\OrderMapper;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateOrderUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly GirlStaffValidator $staffValidator,
        private readonly ServiceAreaRepositoryInterface $serviceAreas,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateOrderInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null) {
            throw OrderDomainException::branchRequired();
        }

        if ($branch === null || $userId === null) {
            throw OrderDomainException::branchRequired();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);
        $staffRole = $this->staffContext->staffRole();

        $isWaiter = $staffRole === 'WAITER' || $this->staffContext->roleSlug() === 'waiter';

        if ($isWaiter) {
            $waiterId = $userId;
        } else {
            if ($input->waiterUserId === null || $input->waiterUserId <= 0) {
                throw OrderDomainException::waiterRequired();
            }

            $waiterId = $input->waiterUserId;
            $this->staffValidator->assertWaiter($tenant->id, $waiterId);
        }

        $tableLabel = $input->tableLabel !== null ? trim($input->tableLabel) : null;
        $serviceAreaId = $input->serviceAreaId;

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

        $order = $this->orders->create(
            tenantId: $tenant->id,
            branchId: $branch->id,
            officialShiftId: $shift->id,
            orderNumber: $this->orders->nextOrderNumber($branch->id),
            tableLabel: $tableLabel,
            serviceAreaId: $serviceAreaId > 0 ? $serviceAreaId : null,
            waiterUserId: $waiterId,
            openedByUserId: $userId,
            notes: $input->notes,
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'order.created',
            [
                'entity'  => ['type' => 'order', 'id' => $order->id],
                'summary' => 'Nueva comanda: ' . $order->tableLabel,
                'refresh' => ['orders'],
            ]
        );

        return OperationResult::ok('Comanda abierta correctamente.', [
            'order' => OrderMapper::order($order, false),
        ]);
    }
}
