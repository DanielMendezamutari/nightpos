<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Plan\DTOs\CreatePlanInput;
use App\Application\Plan\DTOs\UpdatePlanInput;
use App\Application\Plan\DTOs\UpdatePlanLimitsInput;
use App\Application\Plan\UseCases\CreatePlanUseCase;
use App\Application\Plan\UseCases\DeletePlanUseCase;
use App\Application\Plan\UseCases\DuplicatePlanUseCase;
use App\Application\Plan\UseCases\GetPlanLimitsUseCase;
use App\Application\Plan\UseCases\ListPlansUseCase;
use App\Application\Plan\UseCases\UpdatePlanLimitsUseCase;
use App\Application\Plan\UseCases\UpdatePlanUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CreatePlanRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePlanLimitsRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePlanRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class PlatformPlanController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListPlansUseCase $listPlans,
        private readonly CreatePlanUseCase $createPlan,
        private readonly UpdatePlanUseCase $updatePlan,
        private readonly DeletePlanUseCase $deletePlan,
        private readonly GetPlanLimitsUseCase $getPlanLimits,
        private readonly UpdatePlanLimitsUseCase $updatePlanLimits,
        private readonly DuplicatePlanUseCase $duplicatePlan,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listPlans->execute());
    }

    public function store(CreatePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createPlan->execute(new CreatePlanInput(
            name: $validated['name'],
            code: $validated['code'],
            description: $validated['description'] ?? null,
            monthlyPrice: (string) $validated['monthly_price'],
            yearlyPrice: (string) $validated['yearly_price'],
            isActive: (bool) ($validated['is_active'] ?? true),
            displayOrder: (int) ($validated['display_order'] ?? 0),
        ));

        return $this->presenter->present($result, 201);
    }

    public function update(int $id, UpdatePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updatePlan->execute(new UpdatePlanInput(
            planId: $id,
            name: $validated['name'],
            code: $validated['code'],
            description: $validated['description'] ?? null,
            monthlyPrice: (string) $validated['monthly_price'],
            yearlyPrice: (string) $validated['yearly_price'],
            isActive: (bool) $validated['is_active'],
            displayOrder: (int) $validated['display_order'],
        ));

        return $this->presenter->present($result);
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->presenter->present($this->deletePlan->execute((object) ['planId' => $id]));
    }

    public function limits(int $id): JsonResponse
    {
        return $this->presenter->present($this->getPlanLimits->execute((object) ['planId' => $id]));
    }

    public function updateLimits(int $id, UpdatePlanLimitsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updatePlanLimits->execute(new UpdatePlanLimitsInput(
            planId: $id,
            limits: $validated['limits'],
        ));

        return $this->presenter->present($result);
    }

    public function duplicate(int $id): JsonResponse
    {
        return $this->presenter->present($this->duplicatePlan->execute((object) ['planId' => $id]), 201);
    }
}
