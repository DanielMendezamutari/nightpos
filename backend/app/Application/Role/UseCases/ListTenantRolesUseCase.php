<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\Support\ManageablePermissionCatalog;
use App\Application\Role\Support\RoleAdminGuard;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListTenantRolesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly RoleAdminGuard $guard,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $roles = RoleModel::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get()
            ->map(fn (RoleModel $role) => $this->guard->mapRole($role))
            ->values()
            ->all();

        return OperationResult::ok('Roles del tenant.', ['roles' => $roles]);
    }
}
