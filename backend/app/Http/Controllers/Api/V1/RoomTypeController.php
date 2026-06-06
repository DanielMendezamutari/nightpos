<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\CreateRoomTypeUseCase;
use App\Application\Settings\UseCases\ListRoomTypesUseCase;
use App\Application\Settings\UseCases\UpdateRoomTypeUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\CreateRoomTypeRequest;
use App\Http\Requests\Api\V1\Settings\UpdateRoomTypeRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class RoomTypeController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListRoomTypesUseCase $listRoomTypes,
        private readonly CreateRoomTypeUseCase $createRoomType,
        private readonly UpdateRoomTypeUseCase $updateRoomType,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listRoomTypes->execute());
    }

    public function store(CreateRoomTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createRoomType->execute((object) [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'defaultDurationMinutes' => $validated['default_duration_minutes'] ?? 60,
            'suggestedPrice' => $validated['suggested_price'] ?? 0,
            'status' => $validated['status'] ?? 'active',
            'branchScoped' => $validated['branch_scoped'] ?? false,
        ]), 201);
    }

    public function update(int $id, UpdateRoomTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateRoomType->execute((object) [
            'id' => $id,
            'name' => $validated['name'],
            'defaultDurationMinutes' => $validated['default_duration_minutes'] ?? null,
            'suggestedPrice' => $validated['suggested_price'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]));
    }
}
