<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Shift\DTOs\CloseOfficialShiftInput;
use App\Application\Shift\DTOs\OpenOfficialShiftInput;
use App\Application\Reports\UseCases\GetShiftClosureCheckUseCase;
use App\Application\Shift\UseCases\CloseOfficialShiftUseCase;
use App\Application\Shift\UseCases\GetCurrentOfficialShiftUseCase;
use App\Application\Shift\UseCases\GetOfficialShiftSummaryUseCase;
use App\Application\Shift\UseCases\GetOfficialShiftUseCase;
use App\Application\Shift\UseCases\ListOfficialShiftsUseCase;
use App\Application\Shift\UseCases\OpenOfficialShiftUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Shift\CloseOfficialShiftRequest;
use App\Http\Requests\Api\V1\Shift\OpenOfficialShiftRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ShiftController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentOfficialShiftUseCase $getCurrent,
        private readonly OpenOfficialShiftUseCase $openShift,
        private readonly CloseOfficialShiftUseCase $closeShift,
        private readonly ListOfficialShiftsUseCase $listShifts,
        private readonly GetOfficialShiftUseCase $getShift,
        private readonly GetOfficialShiftSummaryUseCase $getSummary,
        private readonly GetShiftClosureCheckUseCase $getCloseCheck,
    ) {
    }

    public function current(): JsonResponse
    {
        return $this->presenter->present($this->getCurrent->execute());
    }

    public function closeCheck(): JsonResponse
    {
        return $this->presenter->present($this->getCloseCheck->execute());
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listShifts->execute());
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getShift->execute((object) ['shiftId' => $id]));
    }

    public function summary(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSummary->execute((object) ['shiftId' => $id]));
    }

    public function store(OpenOfficialShiftRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->openShift->execute(new OpenOfficialShiftInput(
            shiftType: $validated['shift_type'],
            businessDate: $validated['business_date'],
            notes: $validated['notes'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function close(int $id, CloseOfficialShiftRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->closeShift->execute(new CloseOfficialShiftInput(
            shiftId: $id,
            countedCash: (string) $validated['counted_cash'],
            notes: $validated['notes'] ?? null,
        ));

        return $this->presenter->present($result);
    }
}
