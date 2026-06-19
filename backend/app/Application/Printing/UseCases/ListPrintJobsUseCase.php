<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobType;

final class ListPrintJobsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PrintJobRepositoryInterface $jobs,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $status = isset($input->status) ? (string) $input->status : null;
        $limit = max(1, min(100, (int) ($input->limit ?? 50)));

        return OperationResult::ok('Historial de impresión.', [
            'jobs' => $this->jobs->listByBranch($tenant->id, $branch->id, $status, $limit),
        ]);
    }
}
