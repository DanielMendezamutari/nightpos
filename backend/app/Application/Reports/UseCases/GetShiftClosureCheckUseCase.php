<?php

declare(strict_types=1);

namespace App\Application\Reports\UseCases;

use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetShiftClosureCheckUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly ReportReadRepositoryInterface $reports,
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $shift = $this->shifts->findOpenForBranch($tenant->id, $branch->id);

        if ($shift === null) {
            return OperationResult::ok('No hay turno abierto.', [
                'shift'     => null,
                'can_close' => false,
                'blockers'  => [['code' => 'no_open_shift', 'message' => 'No hay turno abierto actualmente.', 'count' => 0]],
                'warnings'  => [],
                'summary'   => [],
            ]);
        }

        $check = $this->reports->getShiftClosureCheck($tenant->id, $branch->id, $shift->id);

        return OperationResult::ok('Verificación de cierre de turno.', array_merge(
            ['shift_id' => $shift->id, 'shift_name' => $shift->name],
            $check,
        ));
    }
}
