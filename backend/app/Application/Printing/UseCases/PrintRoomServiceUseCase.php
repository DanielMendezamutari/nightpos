<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Exceptions\RoomServiceNotFoundException;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintRoomServiceUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly RoomServiceRepositoryInterface $roomServices,
        private readonly CreateRoomServicePrintJobUseCase $createPrintJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $roomServiceId = (int) ($input->roomServiceId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);

        $entry = $this->roomServices->findById($roomServiceId, $tenant->id);

        if ($entry === null || (int) ($entry['branch_id'] ?? 0) !== $branch->id) {
            throw new RoomServiceNotFoundException();
        }

        $idempotencyKey = $reprint
            ? "room_service:{$roomServiceId}:reprint:".now()->timestamp
            : "room_service:{$roomServiceId}:v1";

        $printResult = $this->createPrintJob->execute(
            roomServiceId: $roomServiceId,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            idempotencyKey: $idempotencyKey,
        );

        return OperationResult::ok('Ticket de pieza encolado.', [
            'room_service' => $entry,
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }
}
