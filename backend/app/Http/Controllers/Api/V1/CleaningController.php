<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Cleaning\UseCases\GetCleaningShiftEarningsUseCase;
use App\Application\GirlIncome\UseCases\CheckRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\FinishRoomServiceUseCase;
use App\Application\GirlIncome\UseCases\ListActiveRoomServicesUseCase;
use App\Application\GirlIncome\UseCases\ListDueRoomServicesUseCase;
use App\Application\GirlIncome\UseCases\ListRoomControlOverviewUseCase;
use App\Application\Room\UseCases\ListCleaningRoomsUseCase;
use App\Application\Room\UseCases\MarkRoomCleanUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class CleaningController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListRoomControlOverviewUseCase $dashboard,
        private readonly ListCleaningRoomsUseCase $cleaningRooms,
        private readonly ListActiveRoomServicesUseCase $activeServices,
        private readonly ListDueRoomServicesUseCase $dueServices,
        private readonly CheckRoomServiceUseCase $check,
        private readonly FinishRoomServiceUseCase $finish,
        private readonly MarkRoomCleanUseCase $markClean,
        private readonly GetCleaningShiftEarningsUseCase $shiftEarnings,
    ) {
    }

    public function dashboard(): JsonResponse
    {
        return $this->presenter->present($this->dashboard->execute());
    }

    public function rooms(): JsonResponse
    {
        return $this->presenter->present($this->cleaningRooms->execute());
    }

    public function activeServices(): JsonResponse
    {
        return $this->presenter->present($this->activeServices->execute());
    }

    public function dueServices(): JsonResponse
    {
        return $this->presenter->present($this->dueServices->execute());
    }

    public function check(int $id): JsonResponse
    {
        return $this->presenter->present($this->check->execute((object) ['roomServiceId' => $id]));
    }

    public function finish(int $id): JsonResponse
    {
        return $this->presenter->present($this->finish->execute((object) ['roomServiceId' => $id]));
    }

    public function markClean(int $id): JsonResponse
    {
        return $this->presenter->present($this->markClean->execute((object) ['roomId' => $id]));
    }

    public function shiftEarnings(): JsonResponse
    {
        return $this->presenter->present($this->shiftEarnings->execute());
    }
}
