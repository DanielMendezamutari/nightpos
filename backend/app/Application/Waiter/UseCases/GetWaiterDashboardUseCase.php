<?php

declare(strict_types=1);

namespace App\Application\Waiter\UseCases;

use App\Application\Order\Support\OrderListScopeResolver;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Application\Waiter\Support\WaiterOrderMapper;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetWaiterDashboardUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $access,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $waiterId = $this->access->waiterUserId();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $waiterId === null || $userId === null) {
            throw UserDomainException::branchNotInTenant();
        }

        if (! $this->access->isWaiter()) {
            return OperationResult::fail('Solo disponible para garzones.');
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);
        $activeStatuses = OrderListScopeResolver::OPERATIONAL_ACTIVE;

        $cards = [
            'active_tables' => $this->orders->countForWaiter(
                $tenant->id,
                $branch->id,
                $waiterId,
                statuses: $activeStatuses,
                officialShiftId: $shift->id,
            ),
            'open_orders' => $this->orders->countForWaiter(
                $tenant->id,
                $branch->id,
                $waiterId,
                status: 'OPEN',
                officialShiftId: $shift->id,
            ),
            'sent_to_bar' => $this->orders->countForWaiter(
                $tenant->id,
                $branch->id,
                $waiterId,
                status: 'SENT_TO_BAR',
                officialShiftId: $shift->id,
            ),
            'pending_charge' => $this->orders->countForWaiter(
                $tenant->id,
                $branch->id,
                $waiterId,
                statuses: OrderListScopeResolver::PENDING_CHARGE_BAR_ONLY,
                officialShiftId: $shift->id,
            ),
        ];

        $recent = $this->orders->listForWaiter(
            $tenant->id,
            $branch->id,
            $waiterId,
            statuses: $activeStatuses,
            officialShiftId: $shift->id,
        );

        $preview = array_slice(array_map(
            static fn ($order) => WaiterOrderMapper::card($order),
            $recent,
        ), 0, 6);

        return OperationResult::ok('Panel garzón.', [
            'dashboard' => [
                'cards' => $cards,
                'recent_orders' => $preview,
            ],
        ]);
    }
}
