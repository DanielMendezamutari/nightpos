<?php

declare(strict_types=1);

namespace App\Application\Tenant\UseCases;

use App\Application\Tenant\DTOs\CreateTenantInput;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class CreateTenantAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateTenantInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenants->create(
            name: $input->name,
            slug: $input->slug,
            status: $input->status,
            planName: $input->planName,
            subscriptionStartsAt: $input->subscriptionStartsAt,
            subscriptionEndsAt: $input->subscriptionEndsAt,
        );

        return OperationResult::ok('Empresa creada correctamente.', [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
        ]);
    }
}
