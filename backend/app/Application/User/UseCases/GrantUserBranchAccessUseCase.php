<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserBranchAccessInput;
use App\Application\User\Support\UserAdminMapper;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GrantUserBranchAccessUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UserBranchAccessInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $this->users->grantBranchAccess($input->userId, $tenant->id, $input->branchId);

        $model = UserModel::query()
            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->find($input->userId);

        return OperationResult::ok('Sucursal asignada correctamente.', [
            'user' => $model ? UserAdminMapper::user($model) : null,
        ]);
    }
}
