<?php

declare(strict_types=1);

namespace App\Application\Health\UseCases;

use App\Application\Health\Services\HealthCenterEvaluator;
use App\Application\Health\Support\HealthAccessGuard;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetTenantHealthSummaryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly HealthAccessGuard $access,
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly HealthCenterEvaluator $evaluator,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorizeTenant();

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw new TenantNotFoundException();
        }

        $branch = $this->branchContext->branch();
        $branchId = is_object($input) && isset($input->branchId)
            ? (int) $input->branchId
            : ($branch?->id);

        return OperationResult::ok(
            'Resumen de salud operativa.',
            $this->evaluator->evaluateTenant($tenant->id, $branchId),
        );
    }
}
