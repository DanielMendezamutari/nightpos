<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\DTOs\CreateRoleInput;
use App\Application\Role\Support\RoleAdminGuard;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateTenantRoleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly RoleAdminGuard $guard,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        if (! $input instanceof CreateRoleInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $slug = strtolower(trim($input->slug));
        $this->guard->assertSlugAvailable($tenant->id, $slug);

        $role = RoleModel::query()->create([
            'tenant_id' => $tenant->id,
            'name' => trim($input->name),
            'slug' => $slug,
        ]);

        $this->audit->record('role.created', 'role', (int) $role->id, [
            'slug' => $role->slug,
            'name' => $role->name,
        ]);

        return OperationResult::ok('Rol creado.', ['role' => $this->guard->mapRole($role)]);
    }
}
