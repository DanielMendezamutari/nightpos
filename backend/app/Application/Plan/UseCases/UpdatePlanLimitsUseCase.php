<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\DTOs\UpdatePlanLimitsInput;
use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Plan\Exceptions\PlanNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanLimitModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdatePlanLimitsUseCase implements UseCaseInterface
{
    private const ALLOWED_KEYS = ['branches', 'users', 'cashiers', 'waiters', 'products', 'rooms'];

    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdatePlanLimitsInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.update');
        }

        $plan = PlanModel::query()->with('limits')->find($input->planId);

        if ($plan === null) {
            throw PlanNotFoundException::withId($input->planId);
        }

        foreach ($input->limits as $row) {
            $key = $row['limit_key'] ?? '';
            $value = (int) ($row['limit_value'] ?? 0);

            if (! in_array($key, self::ALLOWED_KEYS, true)) {
                continue;
            }

            PlanLimitModel::query()->updateOrCreate(
                ['plan_id' => $plan->id, 'limit_key' => $key],
                ['limit_value' => $value],
            );
        }

        $plan->refresh()->load('limits');

        return OperationResult::ok('Límites actualizados.', [
            'plan' => PlanAdminMapper::plan($plan),
            'limits' => PlanAdminMapper::limits($plan),
        ]);
    }
}
