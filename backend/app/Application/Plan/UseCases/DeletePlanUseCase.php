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

final class DeletePlanUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $planId = is_object($input) && isset($input->planId) ? (int) $input->planId : 0;

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.update');
        }

        $plan = PlanModel::query()->withCount('tenants')->find($planId);

        if ($plan === null) {
            throw PlanNotFoundException::withId($planId);
        }

        if ($plan->tenants_count > 0) {
            $plan->update(['is_active' => false]);

            return OperationResult::ok('Plan desactivado (tiene empresas asignadas).', [
                'plan' => PlanAdminMapper::plan($plan->fresh()->loadCount('tenants'), (int) $plan->tenants_count),
                'deactivated' => true,
            ]);
        }

        $plan->limits()->delete();
        $plan->delete();

        return OperationResult::ok('Plan eliminado correctamente.', [
            'deleted' => true,
        ]);
    }
}
