<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\UseCases;



use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;

use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;

use App\Domain\Room\Repositories\RoomRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class FinishRoomServiceUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly RoomServiceRepositoryInterface $roomServices,

        private readonly RoomRepositoryInterface $rooms,

        private readonly OperationalEventEmitter $eventEmitter,

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



        $entry = $this->roomServices->finish($roomServiceId, $tenant->id, $branch->id, $userId);



        if ($entry === null) {

            throw GirlIncomeDomainException::roomNotActive();

        }



        $roomId = $entry['room_id'] ?? null;

        if ($roomId !== null) {

            $this->rooms->setCleaning((int) $roomId, $tenant->id, $branch->id);

        }



        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'room_service.finished',
            [
                'entity'  => ['type' => 'room_service', 'id' => (int) ($entry['id'] ?? 0)],
                'summary' => 'Pieza finalizada: ' . ($entry['room_label'] ?? $entry['room_number'] ?? ''),
                'refresh' => ['room_services', 'rooms'],
            ]
        );

        return OperationResult::ok('Pieza terminada.', ['room_service' => $entry]);

    }

}


