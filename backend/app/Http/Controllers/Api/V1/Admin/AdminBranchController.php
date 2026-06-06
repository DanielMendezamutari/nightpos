<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Branch\DTOs\CreateBranchInput;
use App\Application\Branch\DTOs\UpdateBranchInput;
use App\Application\Branch\UseCases\CreateBranchAdminUseCase;
use App\Application\Branch\UseCases\GetBranchAdminUseCase;
use App\Application\Branch\UseCases\ListBranchesAdminUseCase;
use App\Application\Branch\UseCases\UpdateBranchAdminUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CreateBranchRequest;
use App\Http\Requests\Api\V1\Admin\UpdateBranchRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class AdminBranchController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListBranchesAdminUseCase $listBranches,
        private readonly CreateBranchAdminUseCase $createBranch,
        private readonly GetBranchAdminUseCase $getBranch,
        private readonly UpdateBranchAdminUseCase $updateBranch,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listBranches->execute());
    }

    public function store(CreateBranchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createBranch->execute(new CreateBranchInput(
            name: $validated['name'],
            code: $validated['code'],
            address: $validated['address'] ?? null,
            status: $validated['status'] ?? 'active',
        ));

        return $this->presenter->present($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getBranch->execute((object) ['branchId' => $id]));
    }

    public function update(int $id, UpdateBranchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateBranch->execute(new UpdateBranchInput(
            branchId: $id,
            name: $validated['name'],
            code: $validated['code'],
            address: $validated['address'] ?? null,
            status: $validated['status'],
        ));

        return $this->presenter->present($result);
    }
}
