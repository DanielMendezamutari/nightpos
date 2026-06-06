<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCurrentOfficialShiftUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $shift = $this->shifts->findOpenForBranch($tenant->id, $branch->id);

        return OperationResult::ok(
            $shift ? 'Turno oficial activo.' : 'Sin turno oficial abierto.',
            ['shift' => $shift ? ShiftMapper::shift($shift) : null],
        );
    }
}
