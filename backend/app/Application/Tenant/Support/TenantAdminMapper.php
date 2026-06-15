<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

use App\Application\Plan\Support\TenantPlanUsageCalculator;
use App\Domain\Tenant\Entities\Tenant;

final class TenantAdminMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function tenant(Tenant $tenant, ?array $planUsage = null): array
    {
        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'plan_id' => $tenant->planId,
            'plan_name' => $tenant->planName,
            'subscription_starts_at' => $tenant->subscriptionStartsAt?->format('Y-m-d'),
            'subscription_ends_at' => $tenant->subscriptionEndsAt?->format('Y-m-d'),
            'plan_usage' => $planUsage,
        ];
    }

    public static function withPlanUsage(Tenant $tenant, TenantPlanUsageCalculator $calculator): array
    {
        $usage = $calculator->forTenant($tenant->id, $tenant->planId);

        return self::tenant($tenant, $usage);
    }
}
