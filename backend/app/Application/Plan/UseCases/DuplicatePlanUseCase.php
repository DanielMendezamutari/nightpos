<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Plan\Exceptions\PlanDomainException;
use App\Domain\Plan\Exceptions\PlanNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanLimitModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class DuplicatePlanUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $planId = is_object($input) && isset($input->planId) ? (int) $input->planId : 0;

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.create');
        }

        $source = PlanModel::query()->with('limits')->find($planId);

        if ($source === null) {
            throw PlanNotFoundException::withId($planId);
        }

        $code = $this->uniqueCode($source->code.'_COPY');

        $plan = PlanModel::query()->create([
            'name' => $source->name.' (copia)',
            'code' => $code,
            'description' => $source->description,
            'monthly_price' => $source->monthly_price,
            'yearly_price' => $source->yearly_price,
            'is_active' => false,
            'display_order' => (int) $source->display_order + 1,
        ]);

        foreach ($source->limits as $limit) {
            PlanLimitModel::query()->create([
                'plan_id' => $plan->id,
                'limit_key' => $limit->limit_key,
                'limit_value' => $limit->limit_value,
            ]);
        }

        return OperationResult::ok('Plan duplicado.', [
            'plan' => PlanAdminMapper::plan($plan->fresh()->load('limits'), 0),
        ]);
    }

    private function uniqueCode(string $base): string
    {
        $code = strtoupper($base);
        $suffix = 1;

        while (PlanModel::query()->where('code', $code)->exists()) {
            $code = strtoupper($base).'_'.$suffix;
            $suffix++;
        }

        if (strlen($code) > 50) {
            throw PlanDomainException::duplicateCode();
        }

        return $code;
    }
}
