<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\ListWaiterTableAssignmentsUseCase;
use App\Application\Settings\UseCases\SyncWaiterTableAssignmentsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\SyncWaiterTableAssignmentsRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class WaiterTableAssignmentController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListWaiterTableAssignmentsUseCase $listAssignments,
        private readonly SyncWaiterTableAssignmentsUseCase $syncAssignments,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listAssignments->execute());
    }

    public function sync(SyncWaiterTableAssignmentsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->syncAssignments->execute((object) [
            'waiterUserId' => (int) $validated['waiter_user_id'],
            'serviceTableIds' => $validated['service_table_ids'] ?? [],
        ]));
    }
}
