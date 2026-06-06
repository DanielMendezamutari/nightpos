<?php

declare(strict_types=1);

namespace App\Application\Cleaning\UseCases;

use App\Domain\Cleaning\Repositories\CleaningTaskRepositoryInterface;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCleaningShiftEarningsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly CleaningTaskRepositoryInterface $cleaningTasks,
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

        $shiftId = $this->settlements->resolveOverviewShiftId($tenant->id, $branch->id);

        if ($shiftId === null) {
            return OperationResult::ok('Sin turno activo.', [
                'earnings' => $this->emptyEarnings(),
            ]);
        }

        $profile = StaffProfileModel::query()
            ->where('user_id', $userId)
            ->where('staff_role', 'CLEANING')
            ->first();

        $baseAmount = $profile?->cleaning_base_amount !== null
            ? (float) $profile->cleaning_base_amount
            : (float) config('nightpos.cleaning.default_base_amount', 30);

        $roomAmount = $profile?->cleaning_room_amount !== null
            ? (float) $profile->cleaning_room_amount
            : (float) config('nightpos.cleaning.default_room_amount', 10);

        $tasks = $this->cleaningTasks->listForShiftAndUser($tenant->id, $branch->id, $shiftId, $userId);
        $roomsCount = count($tasks);
        $roomsTotal = array_reduce($tasks, fn (float $sum, array $t) => $sum + (float) $t['amount'], 0.0);
        $basePaid = $roomsCount > 0 ? $baseAmount : 0.0;

        return OperationResult::ok('Pago del turno.', [
            'earnings' => [
                'official_shift_id' => $shiftId,
                'base_amount' => number_format($baseAmount, 2, '.', ''),
                'room_amount' => number_format($roomAmount, 2, '.', ''),
                'rooms_cleaned' => $roomsCount,
                'rooms_total' => number_format($roomsTotal, 2, '.', ''),
                'base_paid' => number_format($basePaid, 2, '.', ''),
                'total_accumulated' => number_format($basePaid + $roomsTotal, 2, '.', ''),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyEarnings(): array
    {
        return [
            'official_shift_id' => null,
            'base_amount' => '0.00',
            'room_amount' => '0.00',
            'rooms_cleaned' => 0,
            'rooms_total' => '0.00',
            'base_paid' => '0.00',
            'total_accumulated' => '0.00',
        ];
    }
}
