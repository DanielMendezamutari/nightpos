<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Staff\DTOs\QuickCreateGirlInput;
use App\Application\Staff\DTOs\QuickCreateWaiterInput;
use App\Application\Staff\UseCases\ListOperationalGirlsUseCase;
use App\Application\Staff\UseCases\ListOperationalWaitersUseCase;
use App\Application\Staff\UseCases\QuickCreateGirlUseCase;
use App\Application\Staff\UseCases\QuickCreateWaiterUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Staff\QuickCreateGirlRequest;
use App\Http\Requests\Api\V1\Staff\QuickCreateWaiterRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class StaffController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListOperationalGirlsUseCase $listGirls,
        private readonly QuickCreateGirlUseCase $quickCreateGirl,
        private readonly ListOperationalWaitersUseCase $listWaiters,
        private readonly QuickCreateWaiterUseCase $quickCreateWaiter,
    ) {
    }

    public function girls(): JsonResponse
    {
        return $this->presenter->present($this->listGirls->execute());
    }

    public function quickCreateGirl(QuickCreateGirlRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->quickCreateGirl->execute(new QuickCreateGirlInput(
            name: $validated['name'],
            pin: $validated['pin'] ?? null,
            notes: $validated['notes'] ?? null,
            branchId: isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            accessibleBranchIds: array_map('intval', $validated['accessible_branch_ids'] ?? []),
        )), 201);
    }

    public function waiters(): JsonResponse
    {
        return $this->presenter->present($this->listWaiters->execute());
    }

    public function quickCreateWaiter(QuickCreateWaiterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->quickCreateWaiter->execute(new QuickCreateWaiterInput(
            name: $validated['name'],
            pin: $validated['pin'] ?? null,
            waiterCommissionPercent: isset($validated['waiter_commission_percent'])
                ? (string) $validated['waiter_commission_percent']
                : null,
            notes: $validated['notes'] ?? null,
        )), 201);
    }
}
