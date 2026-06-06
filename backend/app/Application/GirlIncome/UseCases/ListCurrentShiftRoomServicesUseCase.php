<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\UseCases;

use App\Application\GirlIncome\Support\GirlIncomeMapper;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListCurrentShiftRoomServicesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly RoomServiceRepositoryInterface $roomServices,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $shift = $this->shifts->findOpenForBranch($tenant->id, $branch->id);

        if ($shift === null) {
            return OperationResult::ok('Sin turno abierto.', [
                'shift' => null,
                'summary' => ['total_amount' => '0.00', 'count' => 0],
                'items' => [],
            ]);
        }

        $summary = $this->roomServices->summarizeForShift($tenant->id, $branch->id, $shift->id);

        return OperationResult::ok('Piezas del turno actual.', [
            'shift' => GirlIncomeMapper::shift($shift),
            'summary' => [
                'total_amount' => number_format($summary['total_amount'], 2, '.', ''),
                'count' => $summary['count'],
            ],
            'items' => $this->roomServices->listForShift($tenant->id, $branch->id, $shift->id),
        ]);
    }
}
