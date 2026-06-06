<?php



declare(strict_types=1);



namespace App\Application\Order\UseCases;



use App\Application\Order\DTOs\ListOrdersInput;

use App\Application\Order\Support\OrderListScopeResolver;

use App\Application\Order\Support\OrderMapper;

use App\Domain\Order\Repositories\OrderRepositoryInterface;

use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class ListOrdersUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly OrderRepositoryInterface $orders,

        private readonly OfficialShiftRepositoryInterface $shifts,

        private readonly OrderListScopeResolver $scopeResolver,

    ) {

    }



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



        if ($scope !== null && $scope !== '') {

            $resolved = $this->scopeResolver->resolve($scope);

            $status = $resolved['status'];

            $statuses = $resolved['statuses'];

            $limit = $resolved['limit'] ?? $limit;

        }



        if ($input instanceof ListOrdersInput && $input->currentShiftOnly) {

            $shiftId = $this->shifts->findOpenForBranch($tenant->id, $branch->id)?->id;

        }



        $items = $this->orders->listForBranch($tenant->id, $branch->id, $status, $shiftId, $statuses);



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



        $data = array_map(

            static fn ($order) => OrderMapper::listBrief(

                $order,

                $order->waiterUserId !== null ? ($waiterNames[$order->waiterUserId] ?? null) : null,

            ),

            $items,

        );



        return OperationResult::ok('Listado de comandas.', ['orders' => $data]);

    }

}

