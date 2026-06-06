<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\GirlIncome\DTOs\CreateRoomServiceInput;
use App\Application\GirlIncome\UseCases\CheckRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\CreateRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\FinishRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\GetRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\ListActiveRoomServicesUseCase;
use App\Application\GirlIncome\UseCases\ListCurrentShiftRoomServicesUseCase;
use App\Application\GirlIncome\UseCases\ListDueRoomServicesUseCase;
use App\Application\GirlIncome\UseCases\ListRoomControlOverviewUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GirlIncome\CreateRoomServiceRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class RoomServiceController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListCurrentShiftRoomServicesUseCase $listCurrent,
        private readonly ListActiveRoomServicesUseCase $listActive,
        private readonly ListDueRoomServicesUseCase $listDue,
        private readonly ListRoomControlOverviewUseCase $listControl,
        private readonly CreateRoomServiceUseCase $create,
        private readonly GetRoomServiceUseCase $get,
        private readonly FinishRoomServiceUseCase $finish,
        private readonly CheckRoomServiceUseCase $check,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listCurrent->execute());
    }

    public function active(): JsonResponse
    {
        return $this->presenter->present($this->listActive->execute());
    }

    public function due(): JsonResponse
    {
        return $this->presenter->present($this->listDue->execute());
    }

    public function control(): JsonResponse
    {
        return $this->presenter->present($this->listControl->execute());
    }

    public function store(CreateRoomServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $total = (string) ($validated['total_amount'] ?? $validated['unit_price']);

        return $this->presenter->present($this->create->execute(new CreateRoomServiceInput(
            girlUserId: (int) $validated['girl_user_id'],
            totalAmount: $total,
            girlPercent: (string) $validated['girl_percent'],
            paymentMethod: (string) $validated['payment_method'],
            roomId: isset($validated['room_id']) ? (int) $validated['room_id'] : null,
            roomLabel: $validated['room_label'] ?? null,
            roomNumber: $validated['room_number'] ?? null,
            durationMinutes: (int) $validated['duration_minutes'],
            startedAt: $validated['started_at'] ?? null,
            notes: $validated['notes'] ?? null,
            cleaningAmount: isset($validated['cleaning_amount']) ? (string) $validated['cleaning_amount'] : null,
        )), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->get->execute((object) ['roomServiceId' => $id]));
    }

    public function finish(int $id): JsonResponse
    {
        return $this->presenter->present($this->finish->execute((object) ['roomServiceId' => $id]));
    }

    public function check(int $id): JsonResponse
    {
        return $this->presenter->present($this->check->execute((object) ['roomServiceId' => $id]));
    }
}
