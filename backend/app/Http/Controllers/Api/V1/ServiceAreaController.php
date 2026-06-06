<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\CreateServiceAreaUseCase;
use App\Application\Settings\UseCases\ListServiceAreasUseCase;
use App\Application\Settings\UseCases\UpdateServiceAreaUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\CreateServiceAreaRequest;
use App\Http\Requests\Api\V1\Settings\UpdateServiceAreaRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ServiceAreaController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListServiceAreasUseCase $listAreas,
        private readonly CreateServiceAreaUseCase $createArea,
        private readonly UpdateServiceAreaUseCase $updateArea,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listAreas->execute());
    }

    public function store(CreateServiceAreaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createArea->execute((object) [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'areaType' => $validated['area_type'] ?? 'TABLE',
            'status' => $validated['status'] ?? 'active',
        ]), 201);
    }

    public function update(int $id, UpdateServiceAreaRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateArea->execute((object) [
            'id' => $id,
            'name' => $validated['name'],
            'areaType' => $validated['area_type'] ?? 'TABLE',
            'status' => $validated['status'] ?? 'active',
        ]));
    }
}
