<?php

declare(strict_types=1);

namespace App\Application\Waiter\UseCases;

use App\Application\Order\Support\OrderListScopeResolver;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Application\Waiter\Support\WaiterOrderMapper;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Http\Request;

final class ListWaiterOrdersUseCase implements UseCaseInterface
{
    private const ACTIVE_STATUSES = ['OPEN', 'SENT_TO_BAR', 'IN_PREPARATION', 'READY'];

    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly WaiterOrderAccessPolicy $access,
        private readonly Request $request,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $waiterId = $this->access->waiterUserId();

        if ($tenant === null || $branch === null || $waiterId === null) {
            throw UserDomainException::branchNotInTenant();
        }

        if (! $this->access->isWaiter()) {
            return OperationResult::fail('Solo disponible para garzones.');
        }

        $shiftId = $this->shifts->findOpenForBranch($tenant->id, $branch->id)?->id;
        $scope = (string) $this->request->query('scope', 'active');

        [$status, $statuses] = $this->resolveScope($scope);

        $items = $this->orders->listForWaiter(
            $tenant->id,
            $branch->id,
            $waiterId,
            $status,
            $statuses,
            $shiftId,
        );

        $data = array_map(
            static fn ($order) => WaiterOrderMapper::card($order),
            $items,
        );

        return OperationResult::ok('Comandas del garzón.', [
            'orders' => $data,
            'scope' => $scope,
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?array<int, string>}
     */
    private function resolveScope(string $scope): array
    {
        return match ($scope) {
            'open' => ['OPEN', null],
            'sent_to_bar' => ['SENT_TO_BAR', null],
            'pending_charge' => [null, OrderListScopeResolver::PENDING_CHARGE_BAR_ONLY],
            'active' => [null, self::ACTIVE_STATUSES],
            default => [null, self::ACTIVE_STATUSES],
        };
    }
}
