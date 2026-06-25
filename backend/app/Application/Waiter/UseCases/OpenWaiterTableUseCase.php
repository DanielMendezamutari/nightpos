<?php

declare(strict_types=1);

namespace App\Application\Waiter\UseCases;

use App\Application\Order\Support\OrderMapper;
use App\Application\Order\Support\OrderOperationalEventPayload;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\ServiceTableRepositoryInterface;
use App\Domain\Settings\Repositories\WaiterTableAssignmentRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class OpenWaiterTableUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly ServiceTableRepositoryInterface $serviceTables,
        private readonly WaiterTableAssignmentRepositoryInterface $assignments,
        private readonly OrderRepositoryInterface $orders,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! is_object($input)) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();
        $tableId = (int) ($input->tableId ?? 0);

        if ($tenant === null || $branch === null || $userId === null || $tableId <= 0) {
            throw OrderDomainException::branchRequired();
        }

        $table = $this->serviceTables->findById($tableId, $tenant->id, $branch->id);
        if ($table === null) {
            throw MasterDataDomainException::notFound();
        }

        if ($table['status'] !== 'active') {
            throw OrderDomainException::serviceTableInactive();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        if (! $this->assignments->isTableAssignedToWaiter(
            $tenant->id,
            $branch->id,
            $userId,
            $tableId,
            $shift->id,
        )) {
            throw OrderDomainException::tableNotAssigned();
        }

        $result = DB::transaction(function () use ($tenant, $branch, $table, $tableId, $userId, $shift) {
            $existing = $this->orders->findActiveByServiceTable(
                $tenant->id,
                $branch->id,
                $tableId,
                $shift->id,
            );

            if ($existing !== null) {
                return ['order' => $existing, 'created' => false, 'status' => 'OCCUPIED'];
            }

            $order = $this->orders->create(
                tenantId: $tenant->id,
                branchId: $branch->id,
                officialShiftId: $shift->id,
                orderNumber: $this->orders->nextOrderNumber($branch->id),
                tableLabel: $table['label'],
                serviceAreaId: (int) $table['service_area_id'],
                serviceTableId: $tableId,
                waiterUserId: $userId,
                openedByUserId: $userId,
                notes: null,
            );

            return ['order' => $order, 'created' => true, 'status' => 'FREE'];
        });

        if ($result['created']) {
            $this->eventEmitter->emit(
                $tenant->id,
                $branch->id,
                'order.created',
                OrderOperationalEventPayload::build(
                    orderId: $result['order']->id,
                    status: OrderStatus::OPEN,
                    source: 'open_waiter_table',
                    summary: 'Nueva comanda: '.$result['order']->tableLabel,
                )
            );
        }

        return OperationResult::ok(
            $result['created'] ? 'Comanda abierta.' : 'Comanda activa recuperada.',
            [
                'table' => [
                    'id' => $tableId,
                    'label' => $table['label'],
                    'area' => $table['service_area_name'] ?? '',
                    'status' => $result['created'] ? 'OCCUPIED' : 'OCCUPIED',
                ],
                'created' => $result['created'],
                'order' => OrderMapper::order($result['order'], false),
            ],
        );
    }
}
