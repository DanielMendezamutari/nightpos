<?php

declare(strict_types=1);

namespace App\Application\ShowType\UseCases;

use App\Domain\ShowType\Repositories\ShowTypeRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListShowTypesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ShowTypeRepositoryInterface $showTypes,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $items = $this->showTypes->listForBranch($tenant->id, $branch->id);

        return OperationResult::ok('Tipos de show.', ['show_types' => $items]);
    }
}
