<?php

declare(strict_types=1);

namespace App\Application\Room\UseCases;

use App\Domain\Room\Exceptions\RoomDomainException;
use App\Domain\Room\Repositories\RoomRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListRoomsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly RoomRepositoryInterface $rooms,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw RoomDomainException::branchRequired();
        }

        $status = $input instanceof \stdClass && isset($input->status) ? (string) $input->status : null;

        return OperationResult::ok('Habitaciones.', [
            'items' => $this->rooms->listForBranch($tenant->id, $branch->id, $status),
            'summary' => $this->rooms->statusSummary($tenant->id, $branch->id),
        ]);
    }
}
