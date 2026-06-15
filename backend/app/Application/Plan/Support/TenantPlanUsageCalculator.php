<?php

declare(strict_types=1);

namespace App\Application\Plan\Support;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanLimitModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class TenantPlanUsageCalculator
{
    public const STATUS_OK = 'OK';
    public const STATUS_WARNING = 'WARNING';
    public const STATUS_LIMIT_REACHED = 'LIMIT_REACHED';

    /**
     * @return array{
     *   plan: array<string, mixed>|null,
     *   limits: list<array<string, mixed>>,
     *   usage: array<string, int>
     * }
     */
    public function forTenant(int $tenantId, ?int $planId): array
    {
        $usage = $this->countUsage($tenantId);

        if ($planId === null) {
            return [
                'plan' => null,
                'limits' => [],
                'usage' => $this->formatUsageRows($usage, []),
            ];
        }

        $plan = PlanModel::query()->with('limits')->find($planId);

        if ($plan === null) {
            return [
                'plan' => null,
                'limits' => [],
                'usage' => $this->formatUsageRows($usage, []),
            ];
        }

        $limitsByKey = $plan->limits->keyBy('limit_key');

        return [
            'plan' => [
                'id' => (int) $plan->id,
                'name' => $plan->name,
                'code' => $plan->code,
                'is_active' => (bool) $plan->is_active,
            ],
            'limits' => $plan->limits
                ->sortBy('limit_key')
                ->values()
                ->map(static fn (PlanLimitModel $limit) => [
                    'key' => $limit->limit_key,
                    'value' => (int) $limit->limit_value,
                ])
                ->all(),
            'usage' => $this->formatUsageRows($usage, $limitsByKey->all()),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function countUsage(int $tenantId): array
    {
        $cashiers = StaffProfileModel::query()
            ->where('tenant_id', $tenantId)
            ->where('staff_role', 'CASHIER')
            ->count();

        $waiters = StaffProfileModel::query()
            ->where('tenant_id', $tenantId)
            ->where('staff_role', 'WAITER')
            ->count();

        return [
            'branches' => BranchModel::query()->where('tenant_id', $tenantId)->count(),
            'users' => UserModel::query()->where('tenant_id', $tenantId)->count(),
            'cashiers' => $cashiers,
            'waiters' => $waiters,
            'products' => ProductModel::query()->where('tenant_id', $tenantId)->count(),
            'rooms' => RoomModel::query()->where('tenant_id', $tenantId)->count(),
        ];
    }

    /**
     * @param array<string, int> $usage
     * @param array<string, PlanLimitModel> $limitsByKey
     * @return list<array<string, mixed>>
     */
    private function formatUsageRows(array $usage, array $limitsByKey): array
    {
        $rows = [];

        foreach ($usage as $key => $current) {
            $limitModel = $limitsByKey[$key] ?? null;
            $limit = $limitModel !== null ? (int) $limitModel->limit_value : null;

            $rows[] = [
                'key' => $key,
                'current' => $current,
                'limit' => $limit,
                'status' => $this->resolveStatus($current, $limit),
            ];
        }

        return $rows;
    }

    private function resolveStatus(int $current, ?int $limit): string
    {
        if ($limit === null) {
            return self::STATUS_OK;
        }

        if ($limit < 0) {
            return self::STATUS_OK;
        }

        if ($current >= $limit) {
            return self::STATUS_LIMIT_REACHED;
        }

        if ($limit > 0 && $current >= (int) ceil($limit * 0.8)) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }
}
