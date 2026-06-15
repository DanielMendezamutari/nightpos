<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListLoginContextTenantsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        $items = array_values(array_map(
            static fn ($tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            array_filter(
                $this->tenants->listAll(),
                static fn ($tenant) => $tenant->isActive() && $tenant->hasValidSubscription(),
            ),
        ));

        return OperationResult::ok('Empresas disponibles para login.', ['tenants' => $items]);
    }
}
