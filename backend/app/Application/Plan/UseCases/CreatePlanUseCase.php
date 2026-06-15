<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\DTOs\CreatePlanInput;
use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Plan\Exceptions\PlanDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreatePlanUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreatePlanInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.create');
        }

        $name = trim($input->name);
        $code = strtoupper(trim($input->code));

        if ($name === '') {
            throw PlanDomainException::emptyName();
        }

        if (PlanModel::query()->where('code', $code)->exists()) {
            throw PlanDomainException::duplicateCode();
        }

        $plan = PlanModel::query()->create([
            'name' => $name,
            'code' => $code,
            'description' => $input->description,
            'monthly_price' => $input->monthlyPrice,
            'yearly_price' => $input->yearlyPrice,
            'is_active' => $input->isActive,
            'display_order' => $input->displayOrder,
        ]);

        return OperationResult::ok('Plan creado correctamente.', [
            'plan' => PlanAdminMapper::plan($plan->fresh(), 0),
        ]);
    }
}
