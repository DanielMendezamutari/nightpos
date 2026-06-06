<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Cash\DTOs\ListCashSessionsAdminInput;
use App\Application\Cash\UseCases\GetCashSessionAdminUseCase;
use App\Application\Cash\UseCases\GetCashSessionsSummaryAdminUseCase;
use App\Application\Cash\UseCases\ListCashSessionsAdminUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AdminCashSessionController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListCashSessionsAdminUseCase $listSessions,
        private readonly GetCashSessionAdminUseCase $getSession,
        private readonly GetCashSessionsSummaryAdminUseCase $getSummary,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listSessions->execute($this->inputFromRequest($request)));
    }

    public function summary(Request $request): JsonResponse
    {
        return $this->presenter->present($this->getSummary->execute($this->inputFromRequest($request)));
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSession->execute((object) ['sessionId' => $id]));
    }

    private function inputFromRequest(Request $request): ListCashSessionsAdminInput
    {
        return new ListCashSessionsAdminInput(
            tenantId: $request->has('tenant_id') ? (int) $request->query('tenant_id') : null,
            branchId: $request->has('branch_id') ? (int) $request->query('branch_id') : null,
            officialShiftId: $request->has('official_shift_id') ? (int) $request->query('official_shift_id') : null,
            cashierUserId: $request->has('cashier_user_id') ? (int) $request->query('cashier_user_id') : null,
            status: $request->has('status') ? (string) $request->query('status') : null,
            dateFrom: $request->has('date_from') ? (string) $request->query('date_from') : null,
            dateTo: $request->has('date_to') ? (string) $request->query('date_to') : null,
        );
    }
}
