<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;
use App\Application\StaffSettlement\Support\SettlementOperationalContextBuilder;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetSettlementPendingSourcesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly SettlementShiftScopeResolver $scopeResolver,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementOperationalContextBuilder $contextBuilder,
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $requestedScope = is_object($input) && isset($input->scope) ? (string) $input->scope : null;
        $scopeInfo = $this->scopeResolver->resolve($tenant->id, $branch->id, $userId, $requestedScope);
        $shiftId = $scopeInfo['shift_id'];
        $openShiftId = $this->settlements->resolveOpenShiftId($tenant->id, $branch->id);

        $operational = $this->contextBuilder->build(
            $this->settlements,
            $tenant->id,
            $branch->id,
            $shiftId,
            $scopeInfo['cash_session_id'],
            $userId,
            $scopeInfo['scope'],
            $openShiftId,
            $scopeInfo['cash_session_shift_id'],
            $scopeInfo['shift_rotated'],
            $scopeInfo['empty_overview'],
        );

        $canViewStaffNames = $this->staffContext->hasPermission('admin.users.list');
        $cashSessionId = $scopeInfo['scope'] === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION
            ? $scopeInfo['cash_session_id']
            : null;

        $staffReadiness = $this->staffReadiness(
            $tenant->id,
            $branch->id,
            $scopeInfo['empty_overview'] ? null : $shiftId,
            $canViewStaffNames,
            $cashSessionId,
        );

        if ($shiftId === null || $scopeInfo['empty_overview']) {
            return OperationResult::ok('Sin turno abierto.', array_merge([
                'active_room_services_count' => 0,
                'unpaid_finished_room_services_count' => 0,
                'unpaid_bracelets_count' => 0,
                'unpaid_shows_count' => 0,
                'unpaid_cleaning_tasks_count' => 0,
            ], $staffReadiness, $operational));
        }

        $activeRoomServicesQuery = RoomServiceModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'ACTIVE');

        if ($cashSessionId !== null) {
            $activeRoomServicesQuery->where('cash_session_id', $cashSessionId);
        }

        $activeRoomServices = $activeRoomServicesQuery->count();

        $finishedRoomsQuery = RoomServiceModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'FINISHED');

        if ($cashSessionId !== null) {
            $finishedRoomsQuery->where('cash_session_id', $cashSessionId);
        }

        $finishedRooms = $finishedRoomsQuery->get();

        $unpaidFinishedRooms = $finishedRooms->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_ROOM'),
        )->count();

        $braceletsQuery = BraceletModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId);

        if ($cashSessionId !== null) {
            $braceletsQuery->where('cash_session_id', $cashSessionId);
        }

        $bracelets = $braceletsQuery->get();

        $unpaidBracelets = $bracelets->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_BRACELET'),
        )->count();

        $showsQuery = ShowModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId);

        if ($cashSessionId !== null) {
            $showsQuery->where('cash_session_id', $cashSessionId);
        }

        $shows = $showsQuery->get();

        $unpaidShows = $shows->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_SHOW'),
        )->count();

        $cleaningTasks = CleaningTaskModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'DONE')
            ->get();

        $unpaidCleaningTasks = $cleaningTasks->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'CLEANING_ROOM'),
        )->count();

        return OperationResult::ok('Fuentes pendientes.', array_merge([
            'active_room_services_count' => $activeRoomServices,
            'unpaid_finished_room_services_count' => $unpaidFinishedRooms,
            'unpaid_bracelets_count' => $unpaidBracelets,
            'unpaid_shows_count' => $unpaidShows,
            'unpaid_cleaning_tasks_count' => $unpaidCleaningTasks,
        ], $staffReadiness, $operational));
    }

    /**
     * @return array{
     *     waiters_without_commission: list<array{id: int, name: string|null}>,
     *     waiters_without_commission_count: int,
     *     girls_without_commission_flag: list<array{id: int, name: string|null}>,
     *     girls_without_commission_flag_count: int
     * }
     */
    private function staffReadiness(int $tenantId, int $branchId, ?int $shiftId, bool $canViewStaffNames, ?int $cashSessionId = null): array
    {
        $waiterIdsWithActivity = $this->waiterIdsWithActivity($tenantId, $branchId, $shiftId, $cashSessionId);
        $girlIdsWithActivity = $this->girlIdsWithActivity($tenantId, $branchId, $shiftId, $cashSessionId);

        $waiters = $waiterIdsWithActivity === []
            ? collect()
            : UserModel::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereIn('id', $waiterIdsWithActivity)
                ->whereHas('staffProfile', function ($query) use ($branchId) {
                    $query->where('staff_role', 'WAITER')
                        ->where('status', 'active')
                        ->where(function ($inner) use ($branchId) {
                            $inner->where('branch_id', $branchId)
                                ->orWhereNull('branch_id');
                        })
                        ->where(function ($inner) {
                            $inner->whereNull('waiter_commission_percent')
                                ->orWhere('waiter_commission_percent', '<=', 0);
                        });
                })
                ->orderBy('name')
                ->get(['id', 'name']);

        $girls = $girlIdsWithActivity === []
            ? collect()
            : UserModel::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereIn('id', $girlIdsWithActivity)
                ->whereHas('staffProfile', function ($query) use ($branchId) {
                    $query->where('staff_role', 'GIRL')
                        ->where('status', 'active')
                        ->where('can_receive_girl_commissions', false)
                        ->where(function ($inner) use ($branchId) {
                            $inner->where('branch_id', $branchId)
                                ->orWhereNull('branch_id');
                        });
                })
                ->orderBy('name')
                ->get(['id', 'name']);

        return [
            'waiters_without_commission' => $waiters->map(static fn (UserModel $u) => [
                'id' => (int) $u->id,
                'name' => $canViewStaffNames ? $u->name : null,
            ])->all(),
            'waiters_without_commission_count' => $waiters->count(),
            'girls_without_commission_flag' => $girls->map(static fn (UserModel $u) => [
                'id' => (int) $u->id,
                'name' => $canViewStaffNames ? $u->name : null,
            ])->all(),
            'girls_without_commission_flag_count' => $girls->count(),
        ];
    }

    /**
     * @return list<int>
     */
    private function waiterIdsWithActivity(int $tenantId, int $branchId, ?int $shiftId, ?int $cashSessionId = null): array
    {
        if ($shiftId === null) {
            return [];
        }

        $query = SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->whereNotNull('waiter_user_id');

        if ($cashSessionId !== null) {
            $query->where('cash_session_id', $cashSessionId);
        }

        return $query->distinct()
            ->pluck('waiter_user_id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function girlIdsWithActivity(int $tenantId, int $branchId, ?int $shiftId, ?int $cashSessionId = null): array
    {
        if ($shiftId === null) {
            return [];
        }

        $ids = collect();

        foreach ([RoomServiceModel::class, BraceletModel::class, ShowModel::class] as $model) {
            $query = $model::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('official_shift_id', $shiftId)
                ->whereNotNull('girl_user_id');

            if ($cashSessionId !== null) {
                $query->where('cash_session_id', $cashSessionId);
            }

            $ids = $ids->merge($query->distinct()->pluck('girl_user_id'));
        }

        $salesQuery = SaleItemModel::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $shiftId)
            ->whereNotNull('sale_items.girl_user_id');

        if ($cashSessionId !== null) {
            $salesQuery->where('sales.cash_session_id', $cashSessionId);
        }

        $ids = $ids->merge($salesQuery->distinct()->pluck('sale_items.girl_user_id'));

        return $ids->map(static fn ($id) => (int) $id)->unique()->values()->all();
    }
}
