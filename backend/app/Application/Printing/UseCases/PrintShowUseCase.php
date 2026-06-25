<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\GirlIncome\Exceptions\ShowNotFoundException;
use App\Domain\GirlIncome\Repositories\ShowRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintShowUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly ShowRepositoryInterface $shows,
        private readonly CreateShowPrintJobUseCase $createPrintJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $showId = (int) ($input->showId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);

        $entry = $this->shows->findById($showId, $tenant->id);

        if ($entry === null || (int) ($entry['branch_id'] ?? 0) !== $branch->id) {
            throw new ShowNotFoundException();
        }

        $idempotencyKey = $reprint
            ? "show:{$showId}:reprint:".now()->timestamp
            : "show:{$showId}:v1";

        $printResult = $this->createPrintJob->execute(
            showId: $showId,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            idempotencyKey: $idempotencyKey,
        );

        return OperationResult::ok('Ticket de show encolado.', [
            'show' => $entry,
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }
}
