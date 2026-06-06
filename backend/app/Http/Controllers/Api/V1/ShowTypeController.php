<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\ShowType\UseCases\CreateShowTypeUseCase;
use App\Application\ShowType\UseCases\ListShowTypesUseCase;
use App\Application\ShowType\UseCases\UpdateShowTypeUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ShowType\CreateShowTypeRequest;
use App\Http\Requests\Api\V1\ShowType\UpdateShowTypeRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ShowTypeController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListShowTypesUseCase $listShowTypes,
        private readonly CreateShowTypeUseCase $createShowType,
        private readonly UpdateShowTypeUseCase $updateShowType,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listShowTypes->execute());
    }

    public function store(CreateShowTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createShowType->execute((object) [
            'name' => $validated['name'],
            'suggestedPrice' => $validated['suggested_price'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]), 201);
    }

    public function update(int $id, UpdateShowTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateShowType->execute((object) [
            'id' => $id,
            'name' => $validated['name'],
            'suggestedPrice' => $validated['suggested_price'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]));
    }
}
