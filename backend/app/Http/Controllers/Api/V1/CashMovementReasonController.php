<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\CreateCashMovementReasonUseCase;
use App\Application\Settings\UseCases\ListCashMovementReasonsUseCase;
use App\Application\Settings\UseCases\UpdateCashMovementReasonUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\CreateCashMovementReasonRequest;
use App\Http\Requests\Api\V1\Settings\UpdateCashMovementReasonRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class CashMovementReasonController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListCashMovementReasonsUseCase $listReasons,
        private readonly CreateCashMovementReasonUseCase $createReason,
        private readonly UpdateCashMovementReasonUseCase $updateReason,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listReasons->execute());
    }

    public function store(CreateCashMovementReasonRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createReason->execute((object) [
            'type' => $validated['type'],
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
            'branchScoped' => $validated['branch_scoped'] ?? false,
        ]), 201);
    }

    public function update(int $id, UpdateCashMovementReasonRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateReason->execute((object) [
            'id' => $id,
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
        ]));
    }
}
