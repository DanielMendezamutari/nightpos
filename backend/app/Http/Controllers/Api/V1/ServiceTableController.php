<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\CreateServiceTableUseCase;
use App\Application\Settings\UseCases\ListServiceTablesUseCase;
use App\Application\Settings\UseCases\UpdateServiceTableUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\CreateServiceTableRequest;
use App\Http\Requests\Api\V1\Settings\UpdateServiceTableRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ServiceTableController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListServiceTablesUseCase $listTables,
        private readonly CreateServiceTableUseCase $createTable,
        private readonly UpdateServiceTableUseCase $updateTable,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listTables->execute());
    }

    public function store(CreateServiceTableRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createTable->execute((object) [
            'serviceAreaId' => (int) $validated['service_area_id'],
            'code' => $validated['code'],
            'label' => $validated['label'],
            'sortOrder' => (int) ($validated['sort_order'] ?? 0),
            'status' => $validated['status'] ?? 'active',
        ]), 201);
    }

    public function update(int $id, UpdateServiceTableRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateTable->execute((object) [
            'id' => $id,
            'label' => $validated['label'],
            'sortOrder' => (int) ($validated['sort_order'] ?? 0),
            'status' => $validated['status'] ?? 'active',
        ]));
    }
}
