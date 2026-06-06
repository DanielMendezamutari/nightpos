<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
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
        private readonly StaffSettlementRepositoryInterface $settlements,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $shiftId = $this->settlements->resolveOverviewShiftId($tenant->id, $branch->id);

        $canViewStaffNames = $this->staffContext->hasPermission('admin.users.list');
        $staffReadiness = $this->staffReadiness($tenant->id, $branch->id, $canViewStaffNames);

        if ($shiftId === null) {
            return OperationResult::ok('Sin turno.', array_merge([
                'active_room_services_count' => 0,
                'unpaid_finished_room_services_count' => 0,
                'unpaid_bracelets_count' => 0,
                'unpaid_shows_count' => 0,
            ], $staffReadiness));
        }

        $activeRoomServices = RoomServiceModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'ACTIVE')
            ->count();

        $finishedRooms = RoomServiceModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->where('status', 'FINISHED')
            ->get();

        $unpaidFinishedRooms = $finishedRooms->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_ROOM'),
        )->count();

        $bracelets = BraceletModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->get();

        $unpaidBracelets = $bracelets->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_BRACELET'),
        )->count();

        $shows = ShowModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('official_shift_id', $shiftId)
            ->get();

        $unpaidShows = $shows->filter(
            fn ($row) => ! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_SHOW'),
        )->count();

        return OperationResult::ok('Fuentes pendientes.', array_merge([
            'active_room_services_count' => $activeRoomServices,
            'unpaid_finished_room_services_count' => $unpaidFinishedRooms,
            'unpaid_bracelets_count' => $unpaidBracelets,
            'unpaid_shows_count' => $unpaidShows,
        ], $staffReadiness));
    }

    /**
     * @return array{
     *     waiters_without_commission: list<array{id: int, name: string}>,
     *     girls_without_commission_flag: list<array{id: int, name: string}>
     * }
     */
    private function staffReadiness(int $tenantId, int $branchId, bool $canViewStaffNames): array
    {
        $waiters = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
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
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->orWhereHas('accessibleBranches', fn ($b) => $b->where('branches.id', $branchId));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $girls = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('staffProfile', function ($query) use ($branchId) {
                $query->where('staff_role', 'GIRL')
                    ->where('status', 'active')
                    ->where('can_receive_girl_commissions', false)
                    ->where(function ($inner) use ($branchId) {
                        $inner->where('branch_id', $branchId)
                            ->orWhereNull('branch_id');
                    });
            })
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->orWhereHas('accessibleBranches', fn ($b) => $b->where('branches.id', $branchId));
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
}
