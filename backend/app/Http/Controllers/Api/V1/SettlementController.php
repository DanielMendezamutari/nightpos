<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\StaffSettlement\DTOs\ListSettlementHistoryInput;
use App\Application\StaffSettlement\UseCases\GenerateCurrentShiftSettlementsUseCase;
use App\Application\StaffSettlement\UseCases\GetCurrentShiftSettlementsUseCase;
use App\Application\StaffSettlement\UseCases\GetSettlementPendingSourcesUseCase;
use App\Application\StaffSettlement\UseCases\GetSettlementUseCase;
use App\Application\StaffSettlement\UseCases\ListSettlementHistoryUseCase;
use App\Application\StaffSettlement\UseCases\MarkSettlementPaidUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settlement\MarkSettlementPaidRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettlementController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentShiftSettlementsUseCase $getCurrent,
        private readonly GenerateCurrentShiftSettlementsUseCase $generate,
        private readonly GetSettlementUseCase $getSettlement,
        private readonly MarkSettlementPaidUseCase $markPaid,
        private readonly ListSettlementHistoryUseCase $listHistory,
        private readonly GetSettlementPendingSourcesUseCase $pendingSources,
    ) {
    }

    public function pendingSources(): JsonResponse
    {
        return $this->presenter->present($this->pendingSources->execute());
    }

    public function currentShift(): JsonResponse
    {
        return $this->presenter->present($this->getCurrent->execute());
    }

    public function generateCurrentShift(): JsonResponse
    {
        return $this->presenter->present($this->generate->execute(), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSettlement->execute((object) ['settlementId' => $id]));
    }

    public function markPaid(int $id, MarkSettlementPaidRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->markPaid->execute((object) [
            'settlementId' => $id,
            'notes' => $validated['notes'] ?? null,
        ]));
    }

    public function history(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listHistory->execute(new ListSettlementHistoryInput(
            limit: (int) $request->query('limit', 50),
            officialShiftId: $request->query('official_shift_id') ? (int) $request->query('official_shift_id') : null,
            staffUserId: $request->query('staff_user_id') ? (int) $request->query('staff_user_id') : null,
            settlementType: $request->query('settlement_type'),
            status: $request->query('status'),
            dateFrom: $request->query('date_from'),
            dateTo: $request->query('date_to'),
        )));
    }
}
