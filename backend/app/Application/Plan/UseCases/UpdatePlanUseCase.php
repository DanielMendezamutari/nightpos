<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\DTOs\UpdatePlanInput;
use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Plan\Exceptions\PlanDomainException;
use App\Domain\Plan\Exceptions\PlanNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdatePlanUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdatePlanInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.update');
        }

        $plan = PlanModel::query()->withCount('tenants')->find($input->planId);

        if ($plan === null) {
            throw PlanNotFoundException::withId($input->planId);
        }

        $name = trim($input->name);
        $code = strtoupper(trim($input->code));

        if ($name === '') {
            throw PlanDomainException::emptyName();
        }

        if (
            PlanModel::query()
                ->where('code', $code)
                ->where('id', '!=', $input->planId)
                ->exists()
        ) {
            throw PlanDomainException::duplicateCode();
        }

        $plan->update([
            'name' => $name,
            'code' => $code,
            'description' => $input->description,
            'monthly_price' => $input->monthlyPrice,
            'yearly_price' => $input->yearlyPrice,
            'is_active' => $input->isActive,
            'display_order' => $input->displayOrder,
        ]);

        return OperationResult::ok('Plan actualizado correctamente.', [
            'plan' => PlanAdminMapper::plan($plan->fresh()->loadCount('tenants'), (int) $plan->tenants_count),
        ]);
    }
}
