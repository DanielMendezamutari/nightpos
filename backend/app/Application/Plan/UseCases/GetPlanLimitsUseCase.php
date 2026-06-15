<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Plan\Exceptions\PlanNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetPlanLimitsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $planId = is_object($input) && isset($input->planId) ? (int) $input->planId : 0;

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.list');
        }

        $plan = PlanModel::query()->with('limits')->find($planId);

        if ($plan === null) {
            throw PlanNotFoundException::withId($planId);
        }

        return OperationResult::ok('Límites del plan.', [
            'plan' => PlanAdminMapper::plan($plan),
            'limits' => PlanAdminMapper::limits($plan),
        ]);
    }
}
