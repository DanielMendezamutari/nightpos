<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListLoginContextBranchesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function execute(mixed $input = null): OperationResult
    {
        $tenantSlug = is_string($input) ? trim($input) : '';

        if ($tenantSlug === '') {
            return OperationResult::fail('Debe indicar la empresa.');
        }

        $tenant = $this->tenants->findBySlug($tenantSlug);

        if ($tenant === null || ! $tenant->isActive() || ! $tenant->hasValidSubscription()) {
            throw new TenantNotFoundException();
        }

        $items = array_values(array_map(
            static fn ($branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
            array_filter(
                $this->branches->listByTenant($tenant->id),
                static fn ($branch) => $branch->isActive(),
            ),
        ));

        return OperationResult::ok('Sucursales disponibles.', ['branches' => $items]);
    }
}
