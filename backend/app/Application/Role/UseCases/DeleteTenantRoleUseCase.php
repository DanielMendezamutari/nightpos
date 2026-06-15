<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\Support\RoleAdminGuard;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class DeleteTenantRoleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly RoleAdminGuard $guard,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        if (! is_int($input)) {
            return OperationResult::fail('Identificador de rol inválido.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $role = $this->guard->resolveTenantRole($input, $tenant->id);
        $this->guard->assertCanDelete($role);

        $metadata = ['slug' => $role->slug, 'name' => $role->name];
        $roleId = (int) $role->id;

        $role->permissions()->detach();
        $role->delete();

        $this->audit->record('role.deleted', 'role', $roleId, $metadata);

        return OperationResult::ok('Rol eliminado.');
    }
}
