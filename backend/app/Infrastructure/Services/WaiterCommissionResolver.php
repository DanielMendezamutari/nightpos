<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;

final class WaiterCommissionResolver
{
    public function resolvePercent(?int $waiterUserId, int $tenantId): ?string
    {
        if ($waiterUserId === null) {
            return null;
        }

        $percent = StaffProfileModel::query()
            ->where('user_id', $waiterUserId)
            ->where('tenant_id', $tenantId)
            ->where('staff_role', 'WAITER')
            ->value('waiter_commission_percent');

        return $percent !== null ? number_format((float) $percent, 2, '.', '') : null;
    }

    public function calculateAmount(string $lineTotal, ?string $percent): ?string
    {
        if ($percent === null) {
            return null;
        }

        $amount = (float) $lineTotal * ((float) $percent / 100);

        return number_format($amount, 2, '.', '');
    }
}
