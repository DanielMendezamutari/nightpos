<?php

declare(strict_types=1);

namespace App\Application\Girl\UseCases;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetGirlShiftEarningsUseCase implements UseCaseInterface
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
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::fail('Debe indicar empresa y sucursal.');
        }

        $profile = StaffProfileModel::query()
            ->where('user_id', $userId)
            ->where('staff_role', 'GIRL')
            ->first();

        if ($profile === null) {
            return OperationResult::fail('Perfil de chica no encontrado.');
        }

        $shiftId = $this->settlements->resolveOverviewShiftId($tenant->id, $branch->id);

        if ($shiftId === null) {
            return OperationResult::ok('Sin turno activo.', [
                'earnings' => $this->emptyEarnings(),
            ]);
        }

        $consumptionPending = $this->sumUnsettledConsumption($tenant->id, $branch->id, $shiftId, $userId);
        $braceletsPending = $this->sumUnsettledBracelets($tenant->id, $branch->id, $shiftId, $userId);
        $roomsPending = $this->sumUnsettledRooms($tenant->id, $branch->id, $shiftId, $userId);
        $showsPending = $this->sumUnsettledShows($tenant->id, $branch->id, $shiftId, $userId);

        $settlementTotals = $this->sumSettlementTotals($tenant->id, $branch->id, $shiftId, $userId);

        $totalPending = $consumptionPending + $braceletsPending + $roomsPending + $showsPending + $settlementTotals['pending'];
        $totalPaid = $settlementTotals['paid'];

        return OperationResult::ok('Ingresos del turno.', [
            'earnings' => [
                'official_shift_id' => $shiftId,
                'consumption_total' => $this->format($consumptionPending + $settlementTotals['consumption_paid']),
                'bracelets_total' => $this->format($braceletsPending + $settlementTotals['bracelets_paid']),
                'rooms_total' => $this->format($roomsPending + $settlementTotals['rooms_paid']),
                'shows_total' => $this->format($showsPending + $settlementTotals['shows_paid']),
                'total_pending' => $this->format($totalPending),
                'total_paid' => $this->format($totalPaid),
            ],
        ]);
    }

    private function sumUnsettledConsumption(int $tenantId, int $branchId, int $shiftId, int $girlUserId): float
    {
        $items = SaleItemModel::query()
            ->select('sale_items.id', 'sale_items.girl_amount_snapshot')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.branch_id', $branchId)
            ->where('sales.official_shift_id', $shiftId)
            ->where('sale_items.girl_user_id', $girlUserId)
            ->where('sale_items.sale_mode', 'CON_ACOMPANANTE')
            ->where('sale_items.girl_amount_snapshot', '>', 0)
            ->get();

        $total = 0.0;

        foreach ($items as $item) {
            if (! $this->settlements->saleItemAlreadySettled((int) $item->id, 'GIRL_CONSUMPTION')) {
                $total += (float) $item->girl_amount_snapshot;
            }
        }

        return $total;
    }

    private function sumUnsettledBracelets(int $tenantId, int $branchId, int $shiftId, int $girlUserId): float
    {
        $rows = BraceletModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->where('girl_user_id', $girlUserId)
            ->get();

        $total = 0.0;

        foreach ($rows as $row) {
            if (! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_BRACELET')) {
                $total += (float) $row->girl_amount;
            }
        }

        return $total;
    }

    private function sumUnsettledRooms(int $tenantId, int $branchId, int $shiftId, int $girlUserId): float
    {
        $rows = RoomServiceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->where('girl_user_id', $girlUserId)
            ->get();

        $total = 0.0;

        foreach ($rows as $row) {
            if (! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_ROOM')) {
                $total += (float) $row->girl_amount;
            }
        }

        return $total;
    }

    private function sumUnsettledShows(int $tenantId, int $branchId, int $shiftId, int $girlUserId): float
    {
        $rows = ShowModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->where('girl_user_id', $girlUserId)
            ->get();

        $total = 0.0;

        foreach ($rows as $row) {
            if (! $this->settlements->sourceAlreadySettled((int) $row->id, 'GIRL_SHOW')) {
                $total += (float) $row->girl_amount;
            }
        }

        return $total;
    }

    /**
     * @return array{pending: float, paid: float, consumption_paid: float, bracelets_paid: float, rooms_paid: float, shows_paid: float}
     */
    private function sumSettlementTotals(int $tenantId, int $branchId, int $shiftId, int $girlUserId): array
    {
        $settlements = StaffSettlementModel::query()
            ->with('items')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $shiftId)
            ->where('staff_user_id', $girlUserId)
            ->where('staff_role', 'GIRL')
            ->get();

        $pending = 0.0;
        $paid = 0.0;
        $consumptionPaid = 0.0;
        $braceletsPaid = 0.0;
        $roomsPaid = 0.0;
        $showsPaid = 0.0;

        foreach ($settlements as $settlement) {
            $items = $settlement->relationLoaded('items') ? $settlement->items : $settlement->items()->get();

            foreach ($items as $item) {
                $amount = (float) $item->amount;

                if ($settlement->status === 'PAID') {
                    $paid += $amount;
                    match ($item->source_type) {
                        'GIRL_CONSUMPTION' => $consumptionPaid += $amount,
                        'GIRL_BRACELET' => $braceletsPaid += $amount,
                        'GIRL_ROOM' => $roomsPaid += $amount,
                        'GIRL_SHOW' => $showsPaid += $amount,
                        default => null,
                    };
                }
                elseif ($settlement->status === 'PENDING') {
                    $pending += $amount;
                }
            }
        }

        return [
            'pending' => $pending,
            'paid' => $paid,
            'consumption_paid' => $consumptionPaid,
            'bracelets_paid' => $braceletsPaid,
            'rooms_paid' => $roomsPaid,
            'shows_paid' => $showsPaid,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyEarnings(): array
    {
        return [
            'official_shift_id' => null,
            'consumption_total' => '0.00',
            'bracelets_total' => '0.00',
            'rooms_total' => '0.00',
            'shows_total' => '0.00',
            'total_pending' => '0.00',
            'total_paid' => '0.00',
        ];
    }

    private function format(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
