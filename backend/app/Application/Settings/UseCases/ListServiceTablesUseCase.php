<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\ServiceTableRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListServiceTablesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ServiceTableRepositoryInterface $tables,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $activeOnly = request()->boolean('active_only');
        $serviceAreaId = request()->filled('service_area_id')
            ? (int) request()->input('service_area_id')
            : null;

        $items = $this->tables->listForBranch(
            $tenant->id,
            $branch->id,
            $activeOnly,
            $serviceAreaId,
        );

        return OperationResult::ok('Mesas listadas.', ['service_tables' => $items]);
    }
}
