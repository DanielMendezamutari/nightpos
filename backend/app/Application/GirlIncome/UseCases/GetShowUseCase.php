<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Exceptions\ShowNotFoundException;
use App\Domain\GirlIncome\Repositories\ShowRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetShowUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ShowRepositoryInterface $shows,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $id = (int) ($input->showId ?? 0);
        $entry = $this->shows->findById($id, $tenant->id);

        if ($entry === null || (int) $entry['branch_id'] !== $branch->id) {
            throw new ShowNotFoundException();
        }

        return OperationResult::ok('Detalle de show.', ['show' => $entry]);
    }
}
