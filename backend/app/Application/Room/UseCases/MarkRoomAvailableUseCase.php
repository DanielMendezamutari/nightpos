<?php

declare(strict_types=1);

namespace App\Application\Room\UseCases;

use App\Domain\Room\Exceptions\RoomDomainException;
use App\Domain\Room\Repositories\RoomRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class MarkRoomAvailableUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly RoomRepositoryInterface $rooms,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $roomId = $input instanceof \stdClass && isset($input->roomId) ? (int) $input->roomId : 0;

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw RoomDomainException::branchRequired();
        }

        $room = $this->rooms->markAvailable($roomId, $tenant->id, $branch->id);

        if ($room === null) {
            throw RoomDomainException::invalidStatusTransition();
        }

        return OperationResult::ok('Habitación disponible.', ['room' => $room]);
    }
}
