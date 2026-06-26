<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Support\Carbon;

final class PlatformOperationsTenantAnalyzer
{
    public const STATUS_ONLINE = 'ONLINE';

    public const STATUS_WARNING = 'WARNING';

    public const STATUS_DEGRADED = 'DEGRADED';

    public const STATUS_OFFLINE = 'OFFLINE';

    public const STATUS_CRITICAL = 'CRITICAL';

    /**
     * @param  array<string, mixed>  $ctx
     * @return array{
     *   operational_status: string,
     *   health_score: int,
     *   last_activity_at: ?string,
     *   last_sale_at: ?string,
     *   main_issue: ?string,
     *   issues: list<array{type: string, message: string, severity: string}>
     * }
     */
    public function analyze(TenantModel $tenant, array $ctx, Carbon $now): array
    {
        $issues = $this->detectIssues($tenant, $ctx, $now);
        $lastActivity = $this->resolveLastActivityAt($ctx);
        $operationalStatus = $this->resolveOperationalStatus($tenant, $ctx, $lastActivity, $issues, $now);
        $healthScore = $this->computeHealthScore($tenant, $ctx, $lastActivity, $issues, $now);
        $mainIssue = $this->resolveMainIssue($issues);

        return [
            'operational_status' => $operationalStatus,
            'health_score' => $healthScore,
            'last_activity_at' => $lastActivity?->toIso8601String(),
            'last_sale_at' => isset($ctx['last_sale_at']) ? (string) $ctx['last_sale_at'] : null,
            'main_issue' => $mainIssue,
            'issues' => $issues,
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function resolveLastActivityAt(array $ctx): ?Carbon
    {
        $candidates = array_filter([
            $ctx['last_sale_at'] ?? null,
            $ctx['last_order_at'] ?? null,
            $ctx['last_audit_at'] ?? null,
            $ctx['last_event_at'] ?? null,
        ]);

        if ($candidates === []) {
            return null;
        }

        $max = null;
        foreach ($candidates as $value) {
            $parsed = Carbon::parse((string) $value);
            if ($max === null || $parsed->greaterThan($max)) {
                $max = $parsed;
            }
        }

        return $max;
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  list<array{type: string, message: string, severity: string}>  $issues
     */
    private function resolveOperationalStatus(
        TenantModel $tenant,
        array $ctx,
        ?Carbon $lastActivity,
        array $issues,
        Carbon $now,
    ): string {
        $criticalTypes = ['TENANT_SUSPENDED', 'TENANT_EXPIRED', 'BOOTSTRAP_INCOMPLETE'];
        foreach ($issues as $issue) {
            if (in_array($issue['type'], $criticalTypes, true) || $issue['severity'] === 'critical') {
                return self::STATUS_CRITICAL;
            }
        }

        $failThreshold = (int) config('nightpos.platform_operations.print_failures_warning_count', 3);
        if (((int) ($ctx['failed_print_jobs_today'] ?? 0)) >= $failThreshold) {
            return self::STATUS_CRITICAL;
        }

        $offlineHours = (int) config('nightpos.platform_operations.activity_offline_hours', 24);
        if ($lastActivity === null || $lastActivity->lessThan($now->copy()->subHours($offlineHours))) {
            return self::STATUS_OFFLINE;
        }

        $devices = $ctx['print_devices'] ?? ['registered' => 0, 'online' => 0, 'offline' => 0];
        if (((int) ($devices['registered'] ?? 0)) > 0 && ((int) ($devices['online'] ?? 0)) === 0) {
            return self::STATUS_DEGRADED;
        }

        if (((int) ($ctx['failed_print_jobs_today'] ?? 0)) > 0) {
            return self::STATUS_DEGRADED;
        }

        $warningHours = (int) config('nightpos.platform_operations.activity_warning_hours', 2);
        if ($lastActivity->lessThan($now->copy()->subHours($warningHours))) {
            return self::STATUS_WARNING;
        }

        $cashHours = (int) config('nightpos.platform_operations.cash_session_warning_hours', 14);
        $oldestCash = $ctx['oldest_cash_opened_at'] ?? null;
        if ($oldestCash instanceof Carbon && $oldestCash->lessThan($now->copy()->subHours($cashHours))) {
            return self::STATUS_WARNING;
        }

        $shiftHours = (int) config('nightpos.platform_operations.shift_warning_hours', 14);
        $oldestShift = $ctx['oldest_shift_opened_at'] ?? null;
        if ($oldestShift instanceof Carbon && $oldestShift->lessThan($now->copy()->subHours($shiftHours))) {
            return self::STATUS_WARNING;
        }

        $noSalesDays = (int) config('nightpos.platform_operations.no_sales_warning_days', 2);
        $lastSale = isset($ctx['last_sale_at']) ? Carbon::parse((string) $ctx['last_sale_at']) : null;
        if ($lastSale === null || $lastSale->lessThan($now->copy()->subDays($noSalesDays))) {
            if ($tenant->status === 'active') {
                return self::STATUS_WARNING;
            }
        }

        $onlineMinutes = (int) config('nightpos.platform_operations.activity_online_minutes', 15);
        if ($lastActivity->greaterThanOrEqualTo($now->copy()->subMinutes($onlineMinutes))) {
            return self::STATUS_ONLINE;
        }

        return self::STATUS_WARNING;
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  list<array{type: string, message: string, severity: string}>  $issues
     */
    private function computeHealthScore(
        TenantModel $tenant,
        array $ctx,
        ?Carbon $lastActivity,
        array $issues,
        Carbon $now,
    ): int {
        $score = 100;

        foreach ($issues as $issue) {
            $score -= match ($issue['type']) {
                'TENANT_SUSPENDED' => 50,
                'TENANT_EXPIRED' => 40,
                'BOOTSTRAP_INCOMPLETE' => 15,
                'PRINT_AGENT_OFFLINE' => 12,
                'PRINT_JOB_FAILED' => 8,
                'CASH_SESSION_TOO_LONG' => 10,
                'SHIFT_NOT_CLOSED' => 10,
                'NO_RECENT_ACTIVITY' => 15,
                default => 5,
            };
        }

        $completion = (float) ($ctx['checklist_completion'] ?? 1.0);
        $score -= (int) round((1.0 - $completion) * 10);

        if ($lastActivity !== null) {
            $hoursAgo = $lastActivity->diffInMinutes($now) / 60;
            if ($hoursAgo > 24) {
                $score -= 20;
            } elseif ($hoursAgo > 2) {
                $score -= 10;
            } elseif ($hoursAgo > 0.25) {
                $score -= 3;
            }
        } else {
            $score -= 25;
        }

        return max(0, min(100, $score));
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return list<array{type: string, message: string, severity: string}>
     */
    private function detectIssues(TenantModel $tenant, array $ctx, Carbon $now): array
    {
        $issues = [];

        if ($tenant->status === 'suspended') {
            $issues[] = [
                'type' => 'TENANT_SUSPENDED',
                'message' => 'Tenant suspendido',
                'severity' => 'critical',
            ];
        }

        if ($tenant->subscription_ends_at !== null && $tenant->subscription_ends_at->lessThan($now)) {
            $issues[] = [
                'type' => 'TENANT_EXPIRED',
                'message' => 'Suscripción vencida',
                'severity' => 'critical',
            ];
        }

        if (! ($ctx['bootstrap_complete'] ?? true)) {
            $issues[] = [
                'type' => 'BOOTSTRAP_INCOMPLETE',
                'message' => 'Bootstrap operativo incompleto',
                'severity' => 'critical',
            ];
        }

        $devices = $ctx['print_devices'] ?? ['registered' => 0, 'online' => 0, 'offline' => 0];
        if (((int) ($devices['registered'] ?? 0)) > 0 && ((int) ($devices['online'] ?? 0)) === 0) {
            $issues[] = [
                'type' => 'PRINT_AGENT_OFFLINE',
                'message' => 'Agente desconectado',
                'severity' => 'warning',
            ];
        }

        $failedToday = (int) ($ctx['failed_print_jobs_today'] ?? 0);
        if ($failedToday > 0) {
            $issues[] = [
                'type' => 'PRINT_JOB_FAILED',
                'message' => $failedToday === 1 ? '1 impresión fallida hoy' : "{$failedToday} impresiones fallidas hoy",
                'severity' => $failedToday >= (int) config('nightpos.platform_operations.print_failures_warning_count', 3)
                    ? 'critical'
                    : 'warning',
            ];
        }

        $cashHours = (int) config('nightpos.platform_operations.cash_session_warning_hours', 14);
        $oldestCash = $ctx['oldest_cash_opened_at'] ?? null;
        if ($oldestCash instanceof Carbon && $oldestCash->lessThan($now->copy()->subHours($cashHours))) {
            $hours = (int) $oldestCash->diffInHours($now);
            $issues[] = [
                'type' => 'CASH_SESSION_TOO_LONG',
                'message' => "Caja abierta hace {$hours} horas",
                'severity' => 'warning',
            ];
        }

        $shiftHours = (int) config('nightpos.platform_operations.shift_warning_hours', 14);
        $oldestShift = $ctx['oldest_shift_opened_at'] ?? null;
        if (((int) ($ctx['open_shifts'] ?? 0)) > 0
            && $oldestShift instanceof Carbon
            && $oldestShift->lessThan($now->copy()->subHours($shiftHours))) {
            $issues[] = [
                'type' => 'SHIFT_NOT_CLOSED',
                'message' => 'Turno sin cierre prolongado',
                'severity' => 'warning',
            ];
        }

        $lastActivity = $this->resolveLastActivityAt($ctx);
        $offlineHours = (int) config('nightpos.platform_operations.activity_offline_hours', 24);
        if ($lastActivity === null || $lastActivity->lessThan($now->copy()->subHours($offlineHours))) {
            $issues[] = [
                'type' => 'NO_RECENT_ACTIVITY',
                'message' => 'Sin actividad reciente',
                'severity' => 'warning',
            ];
        }

        $noSalesDays = (int) config('nightpos.platform_operations.no_sales_warning_days', 2);
        $lastSale = isset($ctx['last_sale_at']) ? Carbon::parse((string) $ctx['last_sale_at']) : null;
        if ($lastSale === null || $lastSale->lessThan($now->copy()->subDays($noSalesDays))) {
            if ($tenant->status === 'active') {
                $issues[] = [
                    'type' => 'NO_RECENT_ACTIVITY',
                    'message' => "No hay ventas hace {$noSalesDays} días",
                    'severity' => 'warning',
                ];
            }
        }

        return $issues;
    }

    /**
     * @param  list<array{type: string, message: string, severity: string}>  $issues
     */
    private function resolveMainIssue(array $issues): ?string
    {
        if ($issues === []) {
            return null;
        }

        usort($issues, static function (array $a, array $b): int {
            $rank = ['critical' => 0, 'warning' => 1, 'info' => 2];

            return ($rank[$a['severity']] ?? 9) <=> ($rank[$b['severity']] ?? 9);
        });

        return $issues[0]['message'];
    }
}
