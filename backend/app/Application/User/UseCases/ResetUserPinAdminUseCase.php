<?php

declare(strict_types=1);

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\ResetPinInput;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ResetUserPinAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly UserRepositoryInterface $users,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof ResetPinInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $this->users->resetPinForTenant($input->userId, $tenant->id, $input->pin);

        return OperationResult::ok('PIN actualizado correctamente.');
    }
}
