<?php

declare(strict_types=1);

namespace App\Application\Health\Services;

use App\Application\Health\Support\HealthOperationalStatus;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class HealthCenterEvaluator
{
    public function __construct(
        private readonly HealthInfrastructureChecker $infrastructure,
        private readonly HealthBranchChecker $branchChecker,
        private readonly HealthScoreCalculator $scoreCalculator,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluatePlatform(): array
    {
        $ttl = max(15, (int) config('nightpos.health_center.cache_seconds', 60));

        return Cache::remember('health_center:platform', $ttl, fn (): array => $this->buildPlatformSummary());
    }

    /**
     * @return array<string, mixed>
     */
    public function evaluateTenant(int $tenantId, ?int $branchId = null): array
    {
        $ttl = max(15, (int) config('nightpos.health_center.cache_seconds', 60));
        $key = 'health_center:tenant:'.$tenantId.':'.($branchId ?? 'all');

        return Cache::remember($key, $ttl, fn (): array => $this->buildTenantSummary($tenantId, $branchId));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlatformSummary(): array
    {
        $now = now();
        $infraChecks = $this->infrastructure->run();
        $infraSummary = $this->scoreCalculator->summarize($infraChecks);

        $tenants = TenantModel::query()->orderBy('name')->get(['id', 'name', 'slug', 'status']);
        $tenantSummaries = [];
        $branchScores = [];

        foreach ($tenants as $tenant) {
            $branches = BranchModel::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('code')
                ->get(['id', 'tenant_id', 'code', 'name', 'status']);

            $branchSummaries = [];

            foreach ($branches as $branch) {
                $branchPayload = $this->evaluateBranch($tenant->id, $branch, $now);
                $branchSummaries[] = $branchPayload;
                $branchScores[] = $branchPayload['score'];
            }

            $tenantScore = $this->aggregateScores(array_column($branchSummaries, 'score'));

            $tenantSummaries[] = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'tenant_slug' => $tenant->slug,
                'tenant_status' => $tenant->status,
                'score' => $tenantScore,
                'status' => HealthOperationalStatus::fromScore($tenantScore),
                'branches' => $branchSummaries,
            ];
        }

        $platformScore = $this->aggregateScores([...$branchScores, $infraSummary['score']]);

        return [
            'evaluated_at' => $now->toIso8601String(),
            'platform' => [
                'score' => $platformScore,
                'status' => HealthOperationalStatus::fromScore($platformScore),
            ],
            'infrastructure' => [
                ...$infraSummary,
                'checks' => $infraChecks,
            ],
            'tenants' => $tenantSummaries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTenantSummary(int $tenantId, ?int $branchId): array
    {
        $now = now();
        $tenant = TenantModel::query()->findOrFail($tenantId);
        $infraChecks = $this->infrastructure->run();
        $infraSummary = $this->scoreCalculator->summarize($infraChecks);

        $branchesQuery = BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('code');

        if ($branchId !== null) {
            $branchesQuery->where('id', $branchId);
        }

        /** @var Collection<int, BranchModel> $branches */
        $branches = $branchesQuery->get(['id', 'tenant_id', 'code', 'name', 'status']);

        $branchSummaries = [];

        foreach ($branches as $branch) {
            $branchSummaries[] = $this->evaluateBranch($tenantId, $branch, $now);
        }

        $branchScores = array_column($branchSummaries, 'score');
        $tenantScore = $this->aggregateScores([...$branchScores, $infraSummary['score']]);

        return [
            'evaluated_at' => $now->toIso8601String(),
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'score' => $tenantScore,
                'status_health' => HealthOperationalStatus::fromScore($tenantScore),
            ],
            'infrastructure' => [
                ...$infraSummary,
                'checks' => $infraChecks,
            ],
            'branches' => $branchSummaries,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function evaluateBranch(int $tenantId, BranchModel $branch, Carbon $now): array
    {
        $checks = $this->branchChecker->run($tenantId, (int) $branch->id, $now);
        $summary = $this->scoreCalculator->summarize($checks);

        return [
            'branch_id' => $branch->id,
            'branch_code' => $branch->code,
            'branch_name' => $branch->name,
            'branch_status' => $branch->status,
            'score' => $summary['score'],
            'status' => $summary['status'],
            'checks' => $checks,
            'alerts' => $summary['alerts'],
        ];
    }

    /**
     * @param  list<int>  $scores
     */
    private function aggregateScores(array $scores): int
    {
        if ($scores === []) {
            return 100;
        }

        return (int) round(array_sum($scores) / count($scores));
    }
}
