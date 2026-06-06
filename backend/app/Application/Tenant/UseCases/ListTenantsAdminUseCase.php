<?php

declare(strict_types=1);

namespace App\Application\Tenant\UseCases;

use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListTenantsAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $items = $this->tenants->listAll();

        $data = array_map(static fn ($tenant) => [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'plan_name' => $tenant->planName,
        ], $items);

        return OperationResult::ok('Listado de empresas.', ['tenants' => $data]);
    }
}
