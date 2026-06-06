<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Room\DTOs\CreateRoomInput;
use App\Application\Room\DTOs\UpdateRoomInput;
use App\Application\Room\UseCases\CreateRoomUseCase;
use App\Application\Room\UseCases\GetRoomUseCase;
use App\Application\Room\UseCases\ListAvailableRoomsUseCase;
use App\Application\Room\UseCases\ListCleaningRoomsUseCase;
use App\Application\Room\UseCases\ListRoomsUseCase;
use App\Application\Room\UseCases\MarkRoomAvailableUseCase;
use App\Application\Room\UseCases\MarkRoomCleanUseCase;
use App\Application\Room\UseCases\MarkRoomMaintenanceUseCase;
use App\Application\Room\UseCases\UpdateRoomUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Room\CreateRoomRequest;
use App\Http\Requests\Api\V1\Room\UpdateRoomRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoomController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListRoomsUseCase $listRooms,
        private readonly ListAvailableRoomsUseCase $listAvailable,
        private readonly ListCleaningRoomsUseCase $listCleaning,
        private readonly GetRoomUseCase $getRoom,
        private readonly CreateRoomUseCase $createRoom,
        private readonly UpdateRoomUseCase $updateRoom,
        private readonly MarkRoomCleanUseCase $markClean,
        private readonly MarkRoomMaintenanceUseCase $markMaintenance,
        private readonly MarkRoomAvailableUseCase $markAvailable,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        return $this->presenter->present($this->listRooms->execute((object) [
            'status' => is_string($status) ? $status : null,
        ]));
    }

    public function available(): JsonResponse
    {
        return $this->presenter->present($this->listAvailable->execute());
    }

    public function cleaning(): JsonResponse
    {
        return $this->presenter->present($this->listCleaning->execute());
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getRoom->execute((object) ['roomId' => $id]));
    }

    public function store(CreateRoomRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createRoom->execute(new CreateRoomInput(
            code: $validated['code'],
            name: $validated['name'],
            roomType: $validated['room_type'] ?? '',
            defaultDurationMinutes: isset($validated['default_duration_minutes'])
                ? (int) $validated['default_duration_minutes']
                : null,
            suggestedPrice: isset($validated['suggested_price'])
                ? (string) $validated['suggested_price']
                : null,
            notes: $validated['notes'] ?? null,
            roomTypeId: isset($validated['room_type_id']) ? (int) $validated['room_type_id'] : null,
        )), 201);
    }

    public function update(int $id, UpdateRoomRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateRoom->execute(new UpdateRoomInput(
            roomId: $id,
            code: $validated['code'],
            name: $validated['name'],
            roomType: $validated['room_type'] ?? '',
            defaultDurationMinutes: isset($validated['default_duration_minutes'])
                ? (int) $validated['default_duration_minutes']
                : null,
            suggestedPrice: isset($validated['suggested_price'])
                ? (string) $validated['suggested_price']
                : null,
            notes: $validated['notes'] ?? null,
            roomTypeId: isset($validated['room_type_id']) ? (int) $validated['room_type_id'] : null,
        )));
    }

    public function markClean(int $id): JsonResponse
    {
        return $this->presenter->present($this->markClean->execute((object) ['roomId' => $id]));
    }

    public function markMaintenance(int $id): JsonResponse
    {
        return $this->presenter->present($this->markMaintenance->execute((object) ['roomId' => $id]));
    }

    public function markAvailable(int $id): JsonResponse
    {
        return $this->presenter->present($this->markAvailable->execute((object) ['roomId' => $id]));
    }
}
