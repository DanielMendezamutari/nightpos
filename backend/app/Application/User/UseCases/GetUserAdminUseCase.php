<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Application\User\Support\UserAdminMapper;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetUserAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        $userId = is_int($input) ? $input : null;

        if ($userId === null) {
            return OperationResult::fail('ID de usuario inválido.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $model = UserModel::query()
            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenant->id)
            ->find($userId);

        if ($model === null) {
            throw new UserNotFoundException();
        }

        return OperationResult::ok('Usuario encontrado.', [
            'user' => UserAdminMapper::user($model),
        ]);
    }
}
