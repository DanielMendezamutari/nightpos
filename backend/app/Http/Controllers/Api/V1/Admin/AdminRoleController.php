<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Role\DTOs\CreateRoleInput;
use App\Application\Role\DTOs\UpdateRoleInput;
use App\Application\Role\DTOs\UpdateRolePermissionsInput;
use App\Application\Role\UseCases\CreateTenantRoleUseCase;
use App\Application\Role\UseCases\DeleteTenantRoleUseCase;
use App\Application\Role\UseCases\GetTenantRoleUseCase;
use App\Application\Role\UseCases\ListManageablePermissionsUseCase;
use App\Application\Role\UseCases\ListTenantRolesUseCase;
use App\Application\Role\UseCases\UpdateTenantRolePermissionsUseCase;
use App\Application\Role\UseCases\UpdateTenantRoleUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CreateRoleRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRolePermissionsRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRoleRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class AdminRoleController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListTenantRolesUseCase $listRoles,
        private readonly ListManageablePermissionsUseCase $listPermissions,
        private readonly GetTenantRoleUseCase $getRole,
        private readonly CreateTenantRoleUseCase $createRole,
        private readonly UpdateTenantRoleUseCase $updateRole,
        private readonly UpdateTenantRolePermissionsUseCase $updatePermissions,
        private readonly DeleteTenantRoleUseCase $deleteRole,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listRoles->execute());
    }

    public function permissions(): JsonResponse
    {
        return $this->presenter->present($this->listPermissions->execute());
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getRole->execute($id));
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present(
            $this->createRole->execute(new CreateRoleInput(
                name: $validated['name'],
                slug: $validated['slug'],
            )),
            201,
        );
    }

    public function update(int $id, UpdateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateRole->execute([
            'role_id' => $id,
            'data' => new UpdateRoleInput(
                name: $validated['name'],
                slug: $validated['slug'],
            ),
        ]));
    }

    public function updatePermissions(int $id, UpdateRolePermissionsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updatePermissions->execute([
            'role_id' => $id,
            'data' => new UpdateRolePermissionsInput(
                permissionSlugs: $validated['permission_slugs'],
            ),
        ]));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->presenter->present($this->deleteRole->execute($id));
    }
}
