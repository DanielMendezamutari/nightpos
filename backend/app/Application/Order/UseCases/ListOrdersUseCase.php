<?php



declare(strict_types=1);



namespace App\Application\Order\UseCases;



use App\Application\Cash\Services\OpenCashSessionResolver;

use App\Application\Order\DTOs\ListOrdersInput;

use App\Application\Order\Services\OrderItemReadinessChecker;

use App\Application\Order\Support\OrderChargeQueueSorter;

use App\Application\Order\Support\OrderListScopeResolver;

use App\Application\Order\Support\OrderMapper;

use App\Application\Order\Support\OrderWaitingMinutesCalculator;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class ListOrdersUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly OrderRepositoryInterface $orders,

        private readonly OfficialShiftRepositoryInterface $shifts,

        private readonly OpenCashSessionResolver $cashSessionResolver,

        private readonly OrderListScopeResolver $scopeResolver,

        private readonly OrderItemReadinessChecker $readinessChecker,

    ) {}



    public function execute(?object $input = null): OperationResult

    {

        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();



        if ($tenant === null || $branch === null) {

            return OperationResult::fail('Contexto operativo incompleto.');

        }



        $status = $input instanceof ListOrdersInput ? $input->status : null;

        $scope = $input instanceof ListOrdersInput ? $input->scope : null;

        $limit = $input instanceof ListOrdersInput ? $input->limit : null;

        $statuses = null;

        $shiftId = null;

        $cashSessionId = null;

        $cashierUserId = null;

        $isCashierChargeable = $scope === 'cashier_chargeable';



        if ($scope !== null && $scope !== '') {

            $resolved = $this->scopeResolver->resolve($scope);

            $status = $resolved['status'];

            $statuses = $resolved['statuses'];

            $limit = $resolved['limit'] ?? $limit;

        }



        $cashierScoped = $input instanceof ListOrdersInput && $input->cashierScoped;

        $currentShiftOnly = $input instanceof ListOrdersInput && $input->currentShiftOnly;

        $currentCashSessionOnly = $input instanceof ListOrdersInput && $input->currentCashSessionOnly;



        if ($cashierScoped || $currentShiftOnly) {

            $shiftId = $this->shifts->findOpenForBranch($tenant->id, $branch->id)?->id;



            if ($shiftId === null) {

                return OperationResult::ok('Listado de comandas.', ['orders' => []]);

            }

        }



        if ($cashierScoped && $currentCashSessionOnly) {

            $userId = $this->staffContext->userId();

            $session = $userId !== null

                ? $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $userId)

                : null;



            if ($session === null) {

                return OperationResult::ok('Listado de comandas.', ['orders' => []]);

            }



            $cashSessionId = $session->id;

            $cashierUserId = $userId;

        }



        $items = $this->orders->listForBranch(

            $tenant->id,

            $branch->id,

            $status,

            $shiftId,

            $statuses,

            $cashSessionId,

            $cashierUserId,

            $isCashierChargeable,

        );



        if ($limit !== null && $limit > 0) {

            $items = array_slice($items, 0, $limit);

        }



        $waiterIds = array_values(array_unique(array_filter(array_map(

            static fn ($order) => $order->waiterUserId,

            $items,

        ))));



        $waiterNames = $waiterIds !== []

            ? UserModel::query()->whereIn('id', $waiterIds)->pluck('name', 'id')->all()

            : [];



        $data = array_map(function ($order) use ($waiterNames, $tenant, $isCashierChargeable) {

            $waiterName = $order->waiterUserId !== null ? ($waiterNames[$order->waiterUserId] ?? null) : null;

            $operational = [];



            if ($isCashierChargeable) {

                $operational = array_merge(

                    ['waiting_minutes' => OrderWaitingMinutesCalculator::fromOrder($order)],

                    $this->readinessChecker->assessOrder($tenant->id, $order),

                );

            }



            return OrderMapper::listBrief($order, $waiterName, $operational);

        }, $items);



        if ($isCashierChargeable) {

            $data = OrderChargeQueueSorter::sort($data);

        }



        return OperationResult::ok('Listado de comandas.', ['orders' => $data]);

    }

}


