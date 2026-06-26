<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashRegisterModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantOperationChecklistItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PlatformOperationsMetricsReader
{
    private Carbon $now;

    private Carbon $todayStart;

    private Carbon $lookbackStart;

    public function __construct(?Carbon $now = null)
    {
        $this->now = $now ?? now();
        $this->todayStart = $this->now->copy()->startOfDay();
        $lookbackDays = max(7, (int) config('nightpos.platform_operations.metrics_lookback_days', 90));
        $this->lookbackStart = $this->now->copy()->subDays($lookbackDays)->startOfDay();
    }

    /**
     * @return Collection<int, TenantModel>
     */
    public function tenantsQuery(): Collection
    {
        return TenantModel::query()
            ->with(['plan'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, string|null>
     */
    public function lastSaleAtByTenant(): array
    {
        return SaleModel::query()
            ->where('paid_at', '>=', $this->lookbackStart)
            ->selectRaw('tenant_id, MAX(paid_at) as last_at')
            ->groupBy('tenant_id')
            ->pluck('last_at', 'tenant_id')
            ->map(fn ($v) => $v !== null ? (string) $v : null)
            ->all();
    }

    /**
     * @return array<int, float>
     */
    public function salesTodayByTenant(): array
    {
        return SaleModel::query()
            ->where('paid_at', '>=', $this->todayStart)
            ->selectRaw('tenant_id, COALESCE(SUM(total), 0) as total')
            ->groupBy('tenant_id')
            ->pluck('total', 'tenant_id')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function ordersTodayByTenant(): array
    {
        return OrderModel::query()
            ->where('created_at', '>=', $this->todayStart)
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, string|null>
     */
    public function lastOrderActivityByTenant(): array
    {
        return OrderModel::query()
            ->where('updated_at', '>=', $this->lookbackStart)
            ->selectRaw('tenant_id, MAX(updated_at) as last_at')
            ->groupBy('tenant_id')
            ->pluck('last_at', 'tenant_id')
            ->map(fn ($v) => $v !== null ? (string) $v : null)
            ->all();
    }

    /**
     * @return array<int, string|null>
     */
    public function lastOperationalEventByTenant(): array
    {
        if (! $this->tableExists('operational_events')) {
            return [];
        }

        return OperationalEventModel::query()
            ->where('created_at', '>=', $this->lookbackStart)
            ->selectRaw('tenant_id, MAX(created_at) as last_at')
            ->groupBy('tenant_id')
            ->pluck('last_at', 'tenant_id')
            ->map(fn ($v) => $v !== null ? (string) $v : null)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function openCashSessionsByTenant(): array
    {
        return CashSessionModel::query()
            ->where('status', 'OPEN')
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, Carbon|null>
     */
    public function oldestOpenCashSessionOpenedAtByTenant(): array
    {
        return CashSessionModel::query()
            ->where('status', 'OPEN')
            ->selectRaw('tenant_id, MIN(opened_at) as oldest')
            ->groupBy('tenant_id')
            ->pluck('oldest', 'tenant_id')
            ->map(fn ($v) => $v !== null ? Carbon::parse($v) : null)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function openShiftsByTenant(): array
    {
        return OfficialShiftModel::query()
            ->where('status', 'OPEN')
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, Carbon|null>
     */
    public function oldestOpenShiftOpenedAtByTenant(): array
    {
        return OfficialShiftModel::query()
            ->where('status', 'OPEN')
            ->selectRaw('tenant_id, MIN(opened_at) as oldest')
            ->groupBy('tenant_id')
            ->pluck('oldest', 'tenant_id')
            ->map(fn ($v) => $v !== null ? Carbon::parse($v) : null)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function failedPrintJobsTodayByTenant(): array
    {
        return PrintJobModel::query()
            ->where('status', 'FAILED')
            ->where('created_at', '>=', $this->todayStart)
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function printJobsTodayByTenant(): array
    {
        return PrintJobModel::query()
            ->where('created_at', '>=', $this->todayStart)
            ->whereIn('status', ['PRINTED', 'FAILED', 'PENDING', 'CLAIMED'])
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return array{online: int, offline: int, total: int}
     */
    public function globalPrintDeviceCounts(): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);
        $threshold = $this->now->copy()->subSeconds($onlineSeconds);

        $total = PrintDeviceModel::query()->where('enabled', true)->count();
        $online = PrintDeviceModel::query()
            ->where('enabled', true)
            ->where('last_seen_at', '>=', $threshold)
            ->count();

        return [
            'total' => $total,
            'online' => $online,
            'offline' => max(0, $total - $online),
        ];
    }

    /**
     * @return array<int, array{online: int, offline: int, registered: int}>
     */
    public function printDevicesByTenant(): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);
        $threshold = $this->now->copy()->subSeconds($onlineSeconds);

        $rows = PrintDeviceModel::query()
            ->where('enabled', true)
            ->selectRaw('tenant_id, last_seen_at')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $tid = (int) $row->tenant_id;
            if (! isset($map[$tid])) {
                $map[$tid] = ['online' => 0, 'offline' => 0, 'registered' => 0];
            }
            $map[$tid]['registered']++;
            if ($row->last_seen_at !== null && $row->last_seen_at->greaterThanOrEqualTo($threshold)) {
                $map[$tid]['online']++;
            } else {
                $map[$tid]['offline']++;
            }
        }

        return $map;
    }

    /**
     * @return array<int, bool>
     */
    public function bootstrapCompleteByTenant(): array
    {
        $paymentCounts = PaymentMethodModel::query()
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->all();

        $registerCounts = CashRegisterModel::query()
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->all();

        $tenantIds = TenantModel::query()->pluck('id');
        $result = [];
        foreach ($tenantIds as $id) {
            $result[(int) $id] = ((int) ($paymentCounts[$id] ?? 0)) > 0
                && ((int) ($registerCounts[$id] ?? 0)) > 0;
        }

        return $result;
    }

    /**
     * @return array<int, float>
     */
    public function checklistCompletionByTenant(): array
    {
        $defaults = count(PlatformOperationsChecklistCatalog::defaults());
        if ($defaults === 0) {
            return [];
        }

        $completed = TenantOperationChecklistItemModel::query()
            ->where('completed', true)
            ->selectRaw('tenant_id, COUNT(*) as cnt')
            ->groupBy('tenant_id')
            ->pluck('cnt', 'tenant_id')
            ->all();

        $result = [];
        foreach (TenantModel::query()->pluck('id') as $id) {
            $result[(int) $id] = min(1.0, ((int) ($completed[$id] ?? 0)) / $defaults);
        }

        return $result;
    }

    public function globalSalesTodayTotal(): float
    {
        return (float) SaleModel::query()
            ->where('paid_at', '>=', $this->todayStart)
            ->sum('total');
    }

    public function globalOrdersTodayCount(): int
    {
        return (int) OrderModel::query()
            ->where('created_at', '>=', $this->todayStart)
            ->count();
    }

    public function globalPrintJobsTodayCount(): int
    {
        return (int) PrintJobModel::query()
            ->where('created_at', '>=', $this->todayStart)
            ->count();
    }

    public function globalFailedPrintJobsTodayCount(): int
    {
        return (int) PrintJobModel::query()
            ->where('created_at', '>=', $this->todayStart)
            ->where('status', 'FAILED')
            ->count();
    }

    /**
     * @return array<int, string|null>
     */
    public function lastAuditAtByTenant(): array
    {
        return AuditLogModel::query()
            ->whereNotNull('tenant_id')
            ->where('created_at', '>=', $this->lookbackStart)
            ->selectRaw('tenant_id, MAX(created_at) as last_at')
            ->groupBy('tenant_id')
            ->pluck('last_at', 'tenant_id')
            ->map(fn ($v) => $v !== null ? (string) $v : null)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function branchesForTenant(int $tenantId): array
    {
        return BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (BranchModel $b) => [
                'id' => (int) $b->id,
                'name' => $b->name,
                'code' => $b->code,
                'status' => $b->status,
            ])
            ->all();
    }

    public function usersCountForTenant(int $tenantId): int
    {
        return (int) UserModel::query()->where('tenant_id', $tenantId)->count();
    }

    public function productsCountForTenant(int $tenantId): int
    {
        return (int) ProductModel::query()->where('tenant_id', $tenantId)->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function printDevicesForTenant(int $tenantId): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);
        $threshold = $this->now->copy()->subSeconds($onlineSeconds);

        return PrintDeviceModel::query()
            ->with('branch:id,name,code')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(function (PrintDeviceModel $d) use ($threshold) {
                $online = $d->last_seen_at !== null && $d->last_seen_at->greaterThanOrEqualTo($threshold);

                return [
                    'id' => (int) $d->id,
                    'name' => $d->name,
                    'branch_id' => (int) $d->branch_id,
                    'branch_name' => $d->branch?->name,
                    'branch_code' => $d->branch?->code,
                    'status' => $d->status,
                    'enabled' => (bool) $d->enabled,
                    'printer_name' => $d->printer_name,
                    'printer_model' => $d->printer_model,
                    'agent_version' => $d->agent_version,
                    'host_name' => $d->host_name,
                    'os_name' => $d->os_name,
                    'os_version' => $d->os_version,
                    'arch' => $d->arch,
                    'ip_address' => $d->ip_address,
                    'last_seen_at' => $d->last_seen_at?->toIso8601String(),
                    'last_printed_at' => $d->last_printed_at?->toIso8601String(),
                    'last_error' => $d->last_error,
                    'installed_at' => $d->installed_at?->toIso8601String(),
                    'online' => $online,
                ];
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function allPrintAgents(): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);
        $threshold = $this->now->copy()->subSeconds($onlineSeconds);

        return PrintDeviceModel::query()
            ->with(['tenant:id,name,slug', 'branch:id,name,code'])
            ->orderBy('tenant_id')
            ->orderBy('name')
            ->get()
            ->map(function (PrintDeviceModel $d) use ($threshold) {
                $online = $d->last_seen_at !== null && $d->last_seen_at->greaterThanOrEqualTo($threshold);

                return [
                    'id' => (int) $d->id,
                    'tenant_id' => (int) $d->tenant_id,
                    'tenant_name' => $d->tenant?->name,
                    'tenant_slug' => $d->tenant?->slug,
                    'branch_id' => (int) $d->branch_id,
                    'branch_name' => $d->branch?->name,
                    'branch_code' => $d->branch?->code,
                    'name' => $d->name,
                    'printer_name' => $d->printer_name,
                    'agent_version' => $d->agent_version,
                    'last_seen_at' => $d->last_seen_at?->toIso8601String(),
                    'last_error' => $d->last_error,
                    'online' => $online,
                ];
            })
            ->all();
    }

    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
}
