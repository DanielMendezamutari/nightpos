<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\DTOs\UpdateRoleInput;
use App\Application\Role\Support\ManageablePermissionCatalog;
use App\Application\Role\Support\RoleAdminGuard;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateTenantRoleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly RoleAdminGuard $guard,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        if (! is_array($input) || ! isset($input['role_id'], $input['data']) || ! $input['data'] instanceof UpdateRoleInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $role = $this->guard->resolveTenantRole((int) $input['role_id'], $tenant->id);
        $dto = $input['data'];
        $slug = strtolower(trim($dto->slug));

        if (ManageablePermissionCatalog::isProtectedRoleSlug($role->slug)) {
            $slug = $role->slug;
        } else {
            $this->guard->assertSlugAvailable($tenant->id, $slug, (int) $role->id);
        }

        $role->update([
            'name' => trim($dto->name),
            'slug' => $slug,
        ]);

        $this->audit->record('role.updated', 'role', (int) $role->id, [
            'slug' => $role->slug,
            'name' => $role->name,
        ]);

        return OperationResult::ok('Rol actualizado.', ['role' => $this->guard->mapRole($role->fresh())]);
    }
}
