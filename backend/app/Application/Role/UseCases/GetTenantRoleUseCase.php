<?php

declare(strict_types=1);

namespace App\Application\Role\UseCases;

use App\Application\Role\Support\RoleAdminGuard;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetTenantRoleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly RoleAdminGuard $guard,
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

        return OperationResult::ok('Detalle del rol.', ['role' => $this->guard->mapRole($role)]);
    }
}
