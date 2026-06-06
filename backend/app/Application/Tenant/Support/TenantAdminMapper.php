<?php

declare(strict_types=1);

namespace App\Application\Tenant\Support;

use App\Domain\Tenant\Entities\Tenant;

final class TenantAdminMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function tenant(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'status' => $tenant->status,
            'plan_name' => $tenant->planName,
            'subscription_starts_at' => $tenant->subscriptionStartsAt?->format('Y-m-d'),
            'subscription_ends_at' => $tenant->subscriptionEndsAt?->format('Y-m-d'),
        ];
    }
}
