<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\UseCases;

use App\Domain\GirlIncome\Exceptions\BraceletNotFoundException;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Repositories\BraceletRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetBraceletUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly BraceletRepositoryInterface $bracelets,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $id = (int) ($input->braceletId ?? 0);
        $entry = $this->bracelets->findById($id, $tenant->id);

        if ($entry === null || (int) $entry['branch_id'] !== $branch->id) {
            throw new BraceletNotFoundException();
        }

        return OperationResult::ok('Detalle de manilla.', ['bracelet' => $entry]);
    }
}
