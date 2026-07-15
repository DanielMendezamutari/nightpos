<?php

declare(strict_types=1);

namespace App\Application\Health\Services;

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Application\Health\Support\HealthSeverity;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Illuminate\Support\Carbon;

final class HealthBranchChecker
{
    public function __construct(
        private readonly DocumentSequenceService $documentSequences,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function run(int $tenantId, int $branchId, ?Carbon $now = null): array
    {
        $now ??= now();

        return [
            $this->checkDocumentSequences($tenantId, $branchId),
            $this->checkOpenCashSessions($tenantId, $branchId, $now),
            $this->checkOpenShifts($tenantId, $branchId, $now),
            $this->checkPendingSettlementsOnClosedShifts($tenantId, $branchId),
            $this->checkSettlementAutoSyncFailures($tenantId, $branchId, $now),
            $this->checkPrintAgent($tenantId, $branchId, $now),
            $this->checkPendingPrintJobs($tenantId, $branchId, $now),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function checkSettlementAutoSyncFailures(int $tenantId, int $branchId, Carbon $now): array
    {
        $windowMinutes = (int) config('nightpos.health_center.settlement_sync_failure_minutes', 30);
        $cutoff = $now->copy()->subMinutes($windowMinutes);

        $rows = AuditLogModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('action', 'settlement.auto_sync_failed')
            ->where('created_at', '>=', $cutoff)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'subject_id', 'metadata', 'created_at']);

        if ($rows->isEmpty()) {
            return $this->ok('SETTLE.AUTO_SYNC', 'Sin fallos recientes de auto-sincronización', ['count' => 0]);
        }

        return $this->issue(
            'SETTLE.AUTO_SYNC',
            $rows->count() === 1
                ? '1 fallo reciente de auto-sincronización de liquidaciones.'
                : $rows->count().' fallos recientes de auto-sincronización de liquidaciones.',
            HealthSeverity::WARNING,
            [
                'count' => $rows->count(),
                'window_minutes' => $windowMinutes,
                'sale_ids' => $rows->pluck('subject_id')->filter()->map(static fn ($id) => (int) $id)->unique()->values()->all(),
                'cash_session_ids' => $rows->map(static fn ($row) => (int) ($row->metadata['cash_session_id'] ?? 0))->filter()->unique()->values()->all(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkDocumentSequences(int $tenantId, int $branchId): array
    {
        $periodKey = (string) now()->year;
        $maxIssued = $this->documentSequences->maxSettlementTicketSequence($tenantId, $branchId, $periodKey);
        $lastValue = $this->documentSequences->currentValue(
            $tenantId,
            $branchId,
            DocumentSequenceType::SettlementPayment,
            $periodKey,
        );

        if ($maxIssued === 0 && $lastValue === null) {
            return $this->ok(
                'DOCSEQ.SETTLEMENT',
                'Sin tickets de liquidación en el periodo',
                ['period_key' => $periodKey, 'max_ticket' => 0],
            );
        }

        if ($lastValue === null && $maxIssued > 0) {
            return $this->issue(
                'DOCSEQ.SETTLEMENT',
                "Falta fila document_sequences (periodo {$periodKey}) con {$maxIssued} ticket(s) emitido(s).",
                HealthSeverity::CRITICAL,
                ['period_key' => $periodKey, 'max_ticket' => $maxIssued, 'last_value' => null],
            );
        }

        if ($lastValue !== null && $lastValue < $maxIssued) {
            return $this->issue(
                'DOCSEQ.SETTLEMENT',
                "Secuencia desfasada: last_value={$lastValue}, max ticket={$maxIssued}.",
                HealthSeverity::CRITICAL,
                ['period_key' => $periodKey, 'last_value' => $lastValue, 'max_ticket' => $maxIssued],
            );
        }

        $row = DocumentSequenceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('document_type', DocumentSequenceType::SettlementPayment->value)
            ->where('period_key', $periodKey)
            ->first();

        return $this->ok(
            'DOCSEQ.SETTLEMENT',
            'Secuencia documental alineada',
            [
                'period_key' => $periodKey,
                'last_value' => $lastValue,
                'max_ticket' => $maxIssued,
                'sequence_id' => $row?->id,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkOpenCashSessions(int $tenantId, int $branchId, Carbon $now): array
    {
        $warningHours = (int) config('nightpos.health_center.cash_session_warning_hours', 14);
        $criticalHours = (int) config('nightpos.health_center.cash_session_critical_hours', 24);

        $sessions = CashSessionModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->orderBy('opened_at')
            ->get(['id', 'opened_at']);

        if ($sessions->isEmpty()) {
            return $this->ok('CASH.OPEN_TTL', 'Sin cajas abiertas', ['open_count' => 0]);
        }

        $oldest = Carbon::parse((string) $sessions->first()->opened_at);
        $hours = (int) $oldest->diffInHours($now);

        if ($hours >= $criticalHours) {
            return $this->issue(
                'CASH.OPEN_TTL',
                "Caja abierta hace {$hours} h (sesión #{$sessions->first()->id}).",
                HealthSeverity::CRITICAL,
                [
                    'open_count' => $sessions->count(),
                    'oldest_session_id' => $sessions->first()->id,
                    'hours_open' => $hours,
                ],
            );
        }

        if ($hours >= $warningHours) {
            return $this->issue(
                'CASH.OPEN_TTL',
                "Caja abierta hace {$hours} h — revisar cierre.",
                HealthSeverity::WARNING,
                [
                    'open_count' => $sessions->count(),
                    'oldest_session_id' => $sessions->first()->id,
                    'hours_open' => $hours,
                ],
            );
        }

        return $this->ok(
            'CASH.OPEN_TTL',
            $sessions->count() === 1 ? 'Caja abierta dentro de plazo' : "{$sessions->count()} cajas abiertas dentro de plazo",
            ['open_count' => $sessions->count(), 'hours_open' => $hours],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkOpenShifts(int $tenantId, int $branchId, Carbon $now): array
    {
        $warningHours = (int) config('nightpos.health_center.shift_warning_hours', 14);
        $criticalHours = (int) config('nightpos.health_center.shift_critical_hours', 24);

        $shifts = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->orderBy('opened_at')
            ->get(['id', 'name', 'opened_at']);

        if ($shifts->isEmpty()) {
            return $this->ok('SHIFT.OPEN_TTL', 'Sin turnos abiertos', ['open_count' => 0]);
        }

        $oldest = Carbon::parse((string) $shifts->first()->opened_at);
        $hours = (int) $oldest->diffInHours($now);

        if ($hours >= $criticalHours) {
            return $this->issue(
                'SHIFT.OPEN_TTL',
                "Turno «{$shifts->first()->name}» abierto hace {$hours} h.",
                HealthSeverity::CRITICAL,
                [
                    'open_count' => $shifts->count(),
                    'oldest_shift_id' => $shifts->first()->id,
                    'hours_open' => $hours,
                ],
            );
        }

        if ($hours >= $warningHours) {
            return $this->issue(
                'SHIFT.OPEN_TTL',
                "Turno abierto hace {$hours} h — revisar cierre.",
                HealthSeverity::WARNING,
                [
                    'open_count' => $shifts->count(),
                    'oldest_shift_id' => $shifts->first()->id,
                    'hours_open' => $hours,
                ],
            );
        }

        return $this->ok(
            'SHIFT.OPEN_TTL',
            'Turno abierto dentro de plazo',
            ['open_count' => $shifts->count(), 'hours_open' => $hours],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkPendingSettlementsOnClosedShifts(int $tenantId, int $branchId): array
    {
        $rows = StaffSettlementModel::query()
            ->from('staff_settlements as ss')
            ->join('official_shifts as os', 'os.id', '=', 'ss.official_shift_id')
            ->where('ss.tenant_id', $tenantId)
            ->where('ss.branch_id', $branchId)
            ->where('ss.status', 'PENDING')
            ->where('os.status', '!=', 'OPEN')
            ->select(['ss.id', 'ss.net_amount', 'ss.official_shift_id', 'os.name as shift_name'])
            ->limit(20)
            ->get();

        $count = $rows->count();

        if ($count === 0) {
            return $this->ok('SETTLE.PENDING_CLOSED_SHIFT', 'Sin liquidaciones pendientes en turnos cerrados', ['count' => 0]);
        }

        $total = round((float) $rows->sum('net_amount'), 2);

        return $this->issue(
            'SETTLE.PENDING_CLOSED_SHIFT',
            $count === 1
                ? "1 liquidación pendiente ({$total} BOB) en turno cerrado."
                : "{$count} liquidaciones pendientes ({$total} BOB) en turnos cerrados.",
            HealthSeverity::CRITICAL,
            [
                'count' => $count,
                'total_amount' => $total,
                'settlement_ids' => $rows->pluck('id')->all(),
                'shift_ids' => $rows->pluck('official_shift_id')->unique()->values()->all(),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkPrintAgent(int $tenantId, int $branchId, Carbon $now): array
    {
        $onlineSeconds = (int) config('nightpos.printing.agent_online_seconds', 120);

        $devices = PrintDeviceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('enabled', true)
            ->get(['id', 'name', 'status', 'last_seen_at']);

        if ($devices->isEmpty()) {
            return $this->ok('PRINT.AGENT_OFFLINE', 'Sin agente registrado en sucursal', ['registered' => 0]);
        }

        $offline = $devices->filter(function ($device) use ($now, $onlineSeconds): bool {
            if ($device->last_seen_at === null) {
                return true;
            }

            return Carbon::parse((string) $device->last_seen_at)->lt($now->copy()->subSeconds($onlineSeconds));
        });

        if ($offline->isEmpty()) {
            return $this->ok(
                'PRINT.AGENT_OFFLINE',
                'Agente de impresión conectado',
                ['registered' => $devices->count(), 'online' => $devices->count()],
            );
        }

        $names = $offline->pluck('name')->implode(', ');

        return $this->issue(
            'PRINT.AGENT_OFFLINE',
            "Agente offline: {$names}.",
            HealthSeverity::WARNING,
            [
                'registered' => $devices->count(),
                'offline_count' => $offline->count(),
                'device_ids' => $offline->pluck('id')->all(),
                'online_threshold_seconds' => $onlineSeconds,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function checkPendingPrintJobs(int $tenantId, int $branchId, Carbon $now): array
    {
        $pendingMinutes = (int) config('nightpos.health_center.print_job_pending_minutes', 15);
        $cutoff = $now->copy()->subMinutes($pendingMinutes);

        $jobs = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'PENDING')
            ->where('created_at', '<', $cutoff)
            ->orderBy('created_at')
            ->limit(20)
            ->get(['id', 'type', 'created_at']);

        $count = $jobs->count();

        if ($count === 0) {
            return $this->ok('PRINT.JOB_PENDING', 'Sin trabajos de impresión pendientes antiguos', ['count' => 0]);
        }

        return $this->issue(
            'PRINT.JOB_PENDING',
            $count === 1
                ? '1 trabajo de impresión pendiente > '.$pendingMinutes.' min.'
                : "{$count} trabajos de impresión pendientes > {$pendingMinutes} min.",
            HealthSeverity::WARNING,
            [
                'count' => $count,
                'job_ids' => $jobs->pluck('id')->all(),
                'pending_minutes' => $pendingMinutes,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function ok(string $code, string $message, array $evidence): array
    {
        return [
            'code' => $code,
            'domain' => 'operations',
            'severity' => HealthSeverity::OK,
            'message' => $message,
            'evidence' => $evidence,
        ];
    }

    /**
     * @param  array<string, mixed>  $evidence
     * @return array<string, mixed>
     */
    private function issue(string $code, string $message, string $severity, array $evidence): array
    {
        return [
            'code' => $code,
            'domain' => 'operations',
            'severity' => $severity,
            'message' => $message,
            'evidence' => $evidence,
        ];
    }
}
