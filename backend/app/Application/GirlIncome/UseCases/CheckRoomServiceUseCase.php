<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CheckRoomServiceUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly RoomServiceRepositoryInterface $roomServices,
        private readonly NotificationRepositoryInterface $notifications,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $roomServiceId = $input instanceof \stdClass && isset($input->roomServiceId)
            ? (int) $input->roomServiceId
            : 0;

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $entry = $this->roomServices->check($roomServiceId, $tenant->id, $branch->id, $userId);

        if ($entry === null) {
            throw GirlIncomeDomainException::notFound();
        }

        $this->notifications->markReadForRoomSource($tenant->id, $branch->id, $roomServiceId);

        return OperationResult::ok('Pieza marcada como revisada.', ['room_service' => $entry]);
    }
}
