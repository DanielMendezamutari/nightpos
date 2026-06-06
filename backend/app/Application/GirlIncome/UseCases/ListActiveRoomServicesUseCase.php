<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListActiveRoomServicesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
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

        return OperationResult::ok('Piezas activas.', [
            'items' => $this->roomServices->listActive($tenant->id, $branch->id),
        ]);
    }
}
