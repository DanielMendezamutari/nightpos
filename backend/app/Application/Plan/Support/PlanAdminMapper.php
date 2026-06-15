<?php

declare(strict_types=1);

namespace App\Application\Plan\Support;

use App\Infrastructure\Persistence\Eloquent\Models\PlanLimitModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;

final class PlanAdminMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function plan(PlanModel $plan, ?int $tenantCount = null): array
    {
        return [
            'id' => (int) $plan->id,
            'name' => $plan->name,
            'code' => $plan->code,
            'description' => $plan->description,
            'monthly_price' => (string) $plan->monthly_price,
            'yearly_price' => (string) $plan->yearly_price,
            'is_active' => (bool) $plan->is_active,
            'display_order' => (int) $plan->display_order,
            'tenants_count' => $tenantCount ?? $plan->tenants()->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function limits(PlanModel $plan): array
    {
        return $plan->limits
            ->sortBy('limit_key')
            ->values()
            ->map(static fn (PlanLimitModel $limit) => [
                'id' => (int) $limit->id,
                'limit_key' => $limit->limit_key,
                'limit_value' => (int) $limit->limit_value,
            ])
            ->all();
    }
}
