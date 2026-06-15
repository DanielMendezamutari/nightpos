<?php

declare(strict_types=1);

namespace App\Application\Platform\UseCases;

use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Carbon;

final class GetPlatformDashboardUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.list');
        }

        $now = Carbon::now();

        $active = TenantModel::query()->where('status', 'active')->count();
        $suspended = TenantModel::query()->where('status', 'suspended')->count();
        $expired = TenantModel::query()
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<', $now)
            ->count();
        $trial = TenantModel::query()
            ->where('status', 'active')
            ->whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '>=', $now)
            ->count();

        $topPlans = PlanModel::query()
            ->withCount('tenants')
            ->orderByDesc('tenants_count')
            ->orderBy('display_order')
            ->limit(5)
            ->get()
            ->map(static fn (PlanModel $plan) => [
                'id' => (int) $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'tenants_count' => (int) $plan->tenants_count,
            ])
            ->all();

        return OperationResult::ok('Dashboard SaaS.', [
            'cards' => [
                'active_tenants' => $active,
                'suspended_tenants' => $suspended,
                'expired_tenants' => $expired,
                'trial_tenants' => $trial,
                'total_tenants' => TenantModel::query()->count(),
            ],
            'top_plans' => $topPlans,
        ]);
    }
}
