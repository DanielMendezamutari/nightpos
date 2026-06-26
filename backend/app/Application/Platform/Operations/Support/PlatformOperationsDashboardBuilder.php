<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class PlatformOperationsDashboardBuilder
{
    public function __construct(
        private readonly PlatformOperationsMetricsReader $metrics,
        private readonly PlatformOperationsTenantAnalyzer $analyzer,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $ttl = max(15, (int) config('nightpos.platform_operations.cache_seconds', 60));

        return Cache::remember('platform_ops:dashboard', $ttl, fn (): array => $this->buildDashboardUncached());
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    public function buildTenantList(array $filters = []): array
    {
        $ttl = max(15, (int) config('nightpos.platform_operations.cache_seconds', 60));
        $cacheKey = 'platform_ops:tenants:'.md5(json_encode($filters) ?: '');

        return Cache::remember($cacheKey, $ttl, fn (): array => $this->buildTenantListUncached($filters));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardUncached(): array
    {
        $now = Carbon::now();
        $reader = $this->metrics;
        $snapshot = $this->loadSnapshot($reader);

        $statusCounts = [
            'online' => 0,
            'warning' => 0,
            'degraded' => 0,
            'offline' => 0,
            'critical' => 0,
        ];

        $problemTenants = [];
        $analyzed = [];

        foreach ($reader->tenantsQuery() as $tenant) {
            $ctx = $this->tenantContext((int) $tenant->id, $snapshot);
            $analysis = $this->analyzer->analyze($tenant, $ctx, $now);
            $analyzed[] = array_merge(
                $this->tenantListItem($tenant, $ctx, $analysis),
                ['issues' => $analysis['issues']],
            );

            $statusKey = strtolower($analysis['operational_status']);
            if (isset($statusCounts[$statusKey])) {
                $statusCounts[$statusKey]++;
            }

            if ($analysis['operational_status'] !== PlatformOperationsTenantAnalyzer::STATUS_ONLINE) {
                $problemTenants[] = [
                    'tenant_id' => (int) $tenant->id,
                    'tenant_name' => $tenant->name,
                    'operational_status' => $analysis['operational_status'],
                    'health_score' => $analysis['health_score'],
                    'main_issue' => $analysis['main_issue'],
                ];
            }
        }

        usort($problemTenants, static fn (array $a, array $b) => ($a['health_score'] ?? 100) <=> ($b['health_score'] ?? 100));

        $printCounts = $reader->globalPrintDeviceCounts();
        $activeTenants = TenantModel::query()->where('status', 'active')->count();

        return [
            'cards' => [
                'total_tenants' => count($analyzed),
                'active_tenants' => $activeTenants,
                'online_tenants' => $statusCounts['online'],
                'warning_tenants' => $statusCounts['warning'],
                'degraded_tenants' => $statusCounts['degraded'],
                'offline_tenants' => $statusCounts['offline'],
                'critical_tenants' => $statusCounts['critical'],
                'print_devices_online' => $printCounts['online'],
                'print_devices_offline' => $printCounts['offline'],
                'sales_today' => $reader->globalSalesTodayTotal(),
                'orders_today' => $reader->globalOrdersTodayCount(),
                'print_jobs_today' => $reader->globalPrintJobsTodayCount(),
                'print_jobs_failed_today' => $reader->globalFailedPrintJobsTodayCount(),
                'critical_errors' => count(array_filter(
                    $analyzed,
                    static fn (array $t) => $t['operational_status'] === PlatformOperationsTenantAnalyzer::STATUS_CRITICAL,
                )),
            ],
            'versions' => [
                'backend_version' => (string) config('nightpos.platform_operations.backend_version', '1.0.0'),
                'frontend_version' => env('NIGHTPOS_FRONTEND_VERSION', env('VITE_APP_VERSION', '1.0.0')),
                'agent_version_note' => 'Por dispositivo en heartbeat',
            ],
            'problem_tenants' => array_slice($problemTenants, 0, 20),
            'latest_critical_issues' => $this->collectLatestIssues($analyzed, 15),
            'backups_status' => 'OK',
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return list<array<string, mixed>>
     */
    private function buildTenantListUncached(array $filters = []): array
    {
        $now = Carbon::now();
        $snapshot = $this->loadSnapshot($this->metrics);
        $items = [];

        foreach ($this->metrics->tenantsQuery() as $tenant) {
            $ctx = $this->tenantContext((int) $tenant->id, $snapshot);
            $analysis = $this->analyzer->analyze($tenant, $ctx, $now);
            $item = $this->tenantListItem($tenant, $ctx, $analysis);

            if (! $this->matchesFilters($item, $ctx, $analysis, $filters)) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildTenantDetail(int $tenantId): ?array
    {
        $tenant = TenantModel::query()->with('plan')->find($tenantId);
        if ($tenant === null) {
            return null;
        }

        $now = Carbon::now();
        $snapshot = $this->loadSnapshot($this->metrics);
        $ctx = $this->tenantContext($tenantId, $snapshot);
        $analysis = $this->analyzer->analyze($tenant, $ctx, $now);
        $branches = $this->metrics->branchesForTenant($tenantId);
        $branchDetails = $this->buildBranchDetails($tenantId, $branches, $now);

        $salesLast7Days = (float) \App\Infrastructure\Persistence\Eloquent\Models\SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('paid_at', '>=', $now->copy()->subDays(7))
            ->sum('total');

        return [
            'summary' => [
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->name,
                'slug' => $tenant->slug,
                'commercial_status' => $tenant->status,
                'plan' => $tenant->plan?->name ?? $tenant->plan_name,
                'health_score' => $analysis['health_score'],
                'operational_status' => $analysis['operational_status'],
                'branches_count' => count($branches),
                'users_count' => $this->metrics->usersCountForTenant($tenantId),
                'last_activity_at' => $analysis['last_activity_at'],
                'last_sale_at' => $analysis['last_sale_at'],
                'sales_today' => (float) ($ctx['sales_today'] ?? 0),
                'sales_last_7_days' => $salesLast7Days,
                'orders_today' => (int) ($ctx['orders_today'] ?? 0),
                'print_jobs_today' => (int) ($ctx['print_jobs_today'] ?? 0),
            ],
            'branches' => $branchDetails,
            'print_agents' => $this->metrics->printDevicesForTenant($tenantId),
            'issues' => $analysis['issues'],
            'installation_checklist' => app(PlatformOperationsChecklistService::class)->listForTenant($tenantId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadSnapshot(PlatformOperationsMetricsReader $reader): array
    {
        return [
            'last_sale_at' => $reader->lastSaleAtByTenant(),
            'last_order_at' => $reader->lastOrderActivityByTenant(),
            'last_audit_at' => $reader->lastAuditAtByTenant(),
            'last_event_at' => $reader->lastOperationalEventByTenant(),
            'sales_today' => $reader->salesTodayByTenant(),
            'orders_today' => $reader->ordersTodayByTenant(),
            'open_cash_sessions' => $reader->openCashSessionsByTenant(),
            'oldest_cash_opened_at' => $reader->oldestOpenCashSessionOpenedAtByTenant(),
            'open_shifts' => $reader->openShiftsByTenant(),
            'oldest_shift_opened_at' => $reader->oldestOpenShiftOpenedAtByTenant(),
            'failed_print_jobs_today' => $reader->failedPrintJobsTodayByTenant(),
            'print_jobs_today' => $reader->printJobsTodayByTenant(),
            'print_devices' => $reader->printDevicesByTenant(),
            'bootstrap_complete' => $reader->bootstrapCompleteByTenant(),
            'checklist_completion' => $reader->checklistCompletionByTenant(),
        ];
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function tenantContext(int $tenantId, array $snapshot): array
    {
        return [
            'last_sale_at' => $snapshot['last_sale_at'][$tenantId] ?? null,
            'last_order_at' => $snapshot['last_order_at'][$tenantId] ?? null,
            'last_audit_at' => $snapshot['last_audit_at'][$tenantId] ?? null,
            'last_event_at' => $snapshot['last_event_at'][$tenantId] ?? null,
            'sales_today' => $snapshot['sales_today'][$tenantId] ?? 0.0,
            'orders_today' => $snapshot['orders_today'][$tenantId] ?? 0,
            'open_cash_sessions' => $snapshot['open_cash_sessions'][$tenantId] ?? 0,
            'oldest_cash_opened_at' => $snapshot['oldest_cash_opened_at'][$tenantId] ?? null,
            'open_shifts' => $snapshot['open_shifts'][$tenantId] ?? 0,
            'oldest_shift_opened_at' => $snapshot['oldest_shift_opened_at'][$tenantId] ?? null,
            'failed_print_jobs_today' => $snapshot['failed_print_jobs_today'][$tenantId] ?? 0,
            'print_jobs_today' => $snapshot['print_jobs_today'][$tenantId] ?? 0,
            'print_devices' => $snapshot['print_devices'][$tenantId] ?? ['online' => 0, 'offline' => 0, 'registered' => 0],
            'bootstrap_complete' => $snapshot['bootstrap_complete'][$tenantId] ?? false,
            'checklist_completion' => $snapshot['checklist_completion'][$tenantId] ?? 0.0,
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  array<string, mixed>  $analysis
     * @return array<string, mixed>
     */
    private function tenantListItem(TenantModel $tenant, array $ctx, array $analysis): array
    {
        $devices = $ctx['print_devices'];

        return [
            'tenant_id' => (int) $tenant->id,
            'tenant_name' => $tenant->name,
            'slug' => $tenant->slug,
            'plan' => $tenant->plan?->name ?? $tenant->plan_name,
            'commercial_status' => $tenant->status,
            'operational_status' => $analysis['operational_status'],
            'health_score' => $analysis['health_score'],
            'last_activity_at' => $analysis['last_activity_at'],
            'last_sale_at' => $analysis['last_sale_at'],
            'sales_today' => (float) ($ctx['sales_today'] ?? 0),
            'orders_today' => (int) ($ctx['orders_today'] ?? 0),
            'open_cash_sessions' => (int) ($ctx['open_cash_sessions'] ?? 0),
            'open_shifts' => (int) ($ctx['open_shifts'] ?? 0),
            'print_devices_online' => (int) ($devices['online'] ?? 0),
            'print_devices_offline' => (int) ($devices['offline'] ?? 0),
            'print_devices_registered' => (int) ($devices['registered'] ?? 0),
            'failed_print_jobs_today' => (int) ($ctx['failed_print_jobs_today'] ?? 0),
            'last_error_at' => null,
            'main_issue' => $analysis['main_issue'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $analyzed
     * @return list<array<string, mixed>>
     */
    private function collectLatestIssues(array $analyzed, int $limit): array
    {
        $all = [];
        foreach ($analyzed as $tenant) {
            foreach ($tenant['issues'] ?? [] as $issue) {
                $all[] = [
                    'tenant_id' => $tenant['tenant_id'],
                    'tenant_name' => $tenant['tenant_name'],
                    'type' => $issue['type'],
                    'message' => $issue['message'],
                    'severity' => $issue['severity'],
                ];
            }
        }

        usort($all, static function (array $a, array $b): int {
            $rank = ['critical' => 0, 'warning' => 1, 'info' => 2];

            return ($rank[$a['severity']] ?? 9) <=> ($rank[$b['severity']] ?? 9);
        });

        unset($tenant);

        return array_slice($all, 0, $limit);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $ctx
     * @param  array<string, mixed>  $analysis
     */
    private function matchesFilters(array $item, array $ctx, array $analysis, array $filters): bool
    {
        if (! empty($filters['status']) && strtoupper((string) $filters['status']) !== $analysis['operational_status']) {
            return false;
        }

        if (! empty($filters['health'])) {
            $healthFilter = (string) $filters['health'];
            $score = (int) $analysis['health_score'];
            $match = match ($healthFilter) {
                'high' => $score >= 80,
                'medium' => $score >= 50 && $score < 80,
                'low' => $score < 50,
                default => true,
            };
            if (! $match) {
                return false;
            }
        }

        if (! empty($filters['agent_offline'])) {
            $devices = $ctx['print_devices'];
            if (((int) ($devices['registered'] ?? 0)) === 0 || ((int) ($devices['online'] ?? 0)) > 0) {
                return false;
            }
        }

        if (! empty($filters['no_sales_today']) && ((float) ($ctx['sales_today'] ?? 0)) > 0) {
            return false;
        }

        if (! empty($filters['open_cash_too_long']) && ! $this->hasIssueType($analysis, 'CASH_SESSION_TOO_LONG')) {
            return false;
        }

        if (! empty($filters['print_errors']) && ((int) ($ctx['failed_print_jobs_today'] ?? 0)) === 0) {
            return false;
        }

        if (! empty($filters['search'])) {
            $q = mb_strtolower((string) $filters['search']);
            $haystack = mb_strtolower($item['tenant_name'].' '.$item['slug']);

            if (! str_contains($haystack, $q)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $analysis
     */
    private function hasIssueType(array $analysis, string $type): bool
    {
        foreach ($analysis['issues'] ?? [] as $issue) {
            if (($issue['type'] ?? '') === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $branches
     * @return list<array<string, mixed>>
     */
    private function buildBranchDetails(int $tenantId, array $branches, Carbon $now): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);
        $threshold = $now->copy()->subSeconds($onlineSeconds);
        $todayStart = $now->copy()->startOfDay();

        $details = [];
        foreach ($branches as $branch) {
            $branchId = (int) $branch['id'];

            $openCash = \App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('status', 'OPEN')
                ->exists();

            $openShift = \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('status', 'OPEN')
                ->exists();

            $salesToday = (float) \App\Infrastructure\Persistence\Eloquent\Models\SaleModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('paid_at', '>=', $todayStart)
                ->sum('total');

            $ordersToday = (int) \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('created_at', '>=', $todayStart)
                ->count();

            $devices = \App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('enabled', true)
                ->get();

            $online = 0;
            $offline = 0;
            $agentVersion = null;
            $lastError = null;
            foreach ($devices as $device) {
                if ($device->last_seen_at !== null && $device->last_seen_at->greaterThanOrEqualTo($threshold)) {
                    $online++;
                } else {
                    $offline++;
                }
                $agentVersion = $device->agent_version ?? $agentVersion;
                $lastError = $device->last_error ?? $lastError;
            }

            $details[] = [
                'branch_id' => $branchId,
                'branch_name' => $branch['name'],
                'branch_code' => $branch['code'],
                'status' => $branch['status'],
                'open_cash_session' => $openCash,
                'open_shift' => $openShift,
                'sales_today' => $salesToday,
                'orders_today' => $ordersToday,
                'print_devices_online' => $online,
                'print_devices_offline' => $devices->isEmpty() ? 0 : $offline,
                'has_registered_agent' => $devices->isNotEmpty(),
                'agent_version' => $agentVersion,
                'last_error' => $lastError,
            ];
        }

        return $details;
    }
}
