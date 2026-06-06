<?php



declare(strict_types=1);



namespace App\Application\Room\UseCases;



use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Cleaning\Repositories\CleaningTaskRepositoryInterface;

use App\Domain\Room\Exceptions\RoomDomainException;

use App\Domain\Room\Repositories\RoomRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class MarkRoomCleanUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly RoomRepositoryInterface $rooms,

        private readonly CleaningTaskRepositoryInterface $cleaningTasks,

        private readonly OperationalEventEmitter $eventEmitter,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        $roomId = $input instanceof \stdClass && isset($input->roomId) ? (int) $input->roomId : 0;



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $userId = $this->staffContext->userId();



        if ($tenant === null || $branch === null || $userId === null) {

            throw RoomDomainException::branchRequired();

        }



        $settledServiceIds = \App\Infrastructure\Persistence\Eloquent\Models\CleaningTaskModel::query()
            ->where('room_id', $roomId)
            ->pluck('room_service_id');

        $lastService = RoomServiceModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('room_id', $roomId)
            ->where('status', 'FINISHED')
            ->when($settledServiceIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $settledServiceIds))
            ->orderByDesc('ended_at')
            ->orderByDesc('id')
            ->first();



        $cleaningTask = null;



        if ($lastService !== null && ! $this->cleaningTasks->existsForRoomService((int) $lastService->id)) {

            $profile = StaffProfileModel::query()

                ->where('user_id', $userId)

                ->where('staff_role', 'CLEANING')

                ->first();



            // Prefer cleaning_amount stored on the room service (deducted from girl's amount)
            // Fall back to staff profile amount, then system default
            $roomPayAmount = $lastService->cleaning_amount !== null && (float) $lastService->cleaning_amount > 0
                ? number_format((float) $lastService->cleaning_amount, 2, '.', '')
                : ($profile?->cleaning_room_amount !== null
                    ? number_format((float) $profile->cleaning_room_amount, 2, '.', '')
                    : number_format((float) config('nightpos.cleaning.default_room_amount', 10), 2, '.', ''));



            $cleaningTask = $this->cleaningTasks->createIfNotExists(

                tenantId: $tenant->id,

                branchId: $branch->id,

                officialShiftId: (int) $lastService->official_shift_id,

                roomId: $roomId,

                roomServiceId: (int) $lastService->id,

                cleaningUserId: $userId,

                amount: $roomPayAmount,

            );

        }



        $room = $this->rooms->markClean($roomId, $tenant->id, $branch->id);



        if ($room === null) {

            throw RoomDomainException::invalidStatusTransition();

        }



        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'room.cleaned',
            [
                'entity'  => ['type' => 'room', 'id' => $roomId],
                'summary' => 'Habitación marcada como limpia: ' . ($room['name'] ?? ''),
                'refresh' => ['rooms', 'room_services'],
            ]
        );

        if ($cleaningTask !== null) {
            $this->eventEmitter->emit(
                $tenant->id,
                $branch->id,
                'cleaning.earnings.updated',
                [
                    'entity'  => ['type' => 'cleaning_task', 'id' => (int) ($cleaningTask['id'] ?? 0)],
                    'summary' => 'Ingreso de limpieza actualizado',
                    'refresh' => ['cleaning_earnings'],
                ],
                'cleaning'
            );
        }

        return OperationResult::ok('Habitación marcada como limpia.', [

            'room' => $room,

            'cleaning_task' => $cleaningTask,

        ]);

    }

}

