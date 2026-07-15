<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Cash\Services\CashPrintPresenter;
use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Printing\Services\CashClosePrintPayloadEnricher;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Cash\Entities\CashSession;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

final class CreateCashClosePrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly CashSessionFinancialSummaryBuilder $financials,
        private readonly CashClosePrintPayloadEnricher $payloadEnricher,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        CashSession $session,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?string $idempotencyKey = null,
        ?string $ticket = null,
        bool $isReprint = false,
        ?int $personnelPage = null,
    ): array {
        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return [
                'job' => null,
                'warning' => 'Caja cerrada, pero no se pudo imprimir el comprobante (sin impresora activa).',
            ];
        }

        $financial = $this->financials->build(
            sessionId: $session->id,
            openingAmount: $session->openingAmount,
            storedExpectedAmount: $session->expectedAmount,
            declaredClosingAmount: $session->declaredClosingAmount,
            differenceAmount: $session->differenceAmount,
            status: $session->status,
        );

        $presented = CashPrintPresenter::cashClose($session, $financial, $tenantId);

        if ($presented === null) {
            return [
                'job' => null,
                'warning' => 'Caja cerrada, pero no se pudo imprimir el comprobante.',
            ];
        }

        $ticketMode = $ticket !== null ? strtolower(trim($ticket)) : 'all';
        if (! in_array($ticketMode, ['all', 'summary', 'personnel'], true)) {
            $ticketMode = 'summary';
        }

        if ($isReprint && $ticketMode === 'personnel') {
            return $this->reprintPersonnelPages(
                tenantId: $tenantId,
                branchId: $branchId,
                requestedByUserId: $requestedByUserId,
                sessionId: $session->id,
                idempotencyKey: $idempotencyKey,
                page: $personnelPage,
            );
        }

        $printedAt = now()->toIso8601String();
        $payload = $this->payloadEnricher->enrich(
            array_merge($presented, ['printed_at' => $printedAt]),
            $tenantId,
            $branchId,
        );

        $summaryResult = ['job' => null, 'warning' => null];
        $personnelResults = [];

        if ($ticketMode === 'summary' || $ticketMode === 'all') {
            $summaryResult = $this->createJob(
                tenantId: $tenantId,
                branchId: $branchId,
                requestedByUserId: $requestedByUserId,
                cashSessionId: $session->id,
                idempotencyKey: $this->ticketKey($session->id, 'summary', $idempotencyKey),
                payload: array_merge($payload, [
                    'ticket_key' => 'summary',
                ]),
                contentText: $this->contentBuilder->buildCashCloseSummaryTicket($payload, printedAt: $printedAt),
            );
        }

        if ($ticketMode === 'personnel' || $ticketMode === 'all') {
            $personnelResults = $this->createPersonnelJobs(
                tenantId: $tenantId,
                branchId: $branchId,
                requestedByUserId: $requestedByUserId,
                cashSessionId: $session->id,
                payload: $payload,
                idempotencyKey: $idempotencyKey,
                printedAt: $printedAt,
                forcedPage: $personnelPage,
            );
        }

        $warning = null;
        $personnelWarnings = array_values(array_filter(array_map(
            static fn (array $result): ?string => $result['warning'] ?? null,
            $personnelResults,
        )));

        if ($summaryResult['warning'] !== null && $personnelWarnings !== []) {
            $warning = 'Caja cerrada, pero no se pudo imprimir el comprobante.';
        } elseif ($summaryResult['warning'] !== null || $personnelWarnings !== []) {
            $warning = 'Caja cerrada, pero uno de los comprobantes no se pudo imprimir.';
        }

        $personnelJobs = array_values(array_filter(array_map(
            static fn (array $result): ?array => $result['job'] ?? null,
            $personnelResults,
        )));

        return [
            'job' => $summaryResult['job'] ?? null,
            'jobs' => array_values(array_filter([
                $summaryResult['job'] ?? null,
                ...$personnelJobs,
            ])),
            'summary_job' => $summaryResult['job'] ?? null,
            'personnel_job' => $personnelJobs[0] ?? null,
            'personnel_jobs' => $personnelJobs,
            'personnel_pages' => count($personnelJobs),
            'warning' => $warning,
            'warnings' => array_values(array_filter([
                $summaryResult['warning'] ?? null,
                ...$personnelWarnings,
            ])),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array{job: ?array<string, mixed>, warning: ?string}>
     */
    private function createPersonnelJobs(
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        int $cashSessionId,
        array $payload,
        ?string $idempotencyKey,
        string $printedAt,
        ?int $forcedPage = null,
    ): array {
        $entries = $this->personnelEntries($payload);
        $pages = $this->paginatePersonnelEntries($entries, 80);
        if ($forcedPage !== null) {
            $pages = array_values(array_filter($pages, static fn (array $page): bool => (int) ($page['page_number'] ?? 0) === $forcedPage));
        }

        $rosterHash = md5(json_encode(array_map(static fn (array $entry): array => [
            'role' => $entry['role'] ?? null,
            'name' => $entry['name'] ?? null,
            'sale' => $entry['sale'] ?? null,
            'pay' => $entry['pay'] ?? null,
        ], $entries)) ?: '');

        $globalTotals = $this->personnelGlobalTotals($entries);
        $results = [];

        foreach ($pages as $page) {
            $pageNumber = (int) ($page['page_number'] ?? 1);
            $pageTotal = (int) ($page['page_total'] ?? 1);
            $snapshotId = "cash_close:{$cashSessionId}:personnel:roster:{$rosterHash}:{$pageTotal}";
            $pagePayload = array_merge($payload, [
                'ticket_key' => 'personnel',
                'personnel_page' => array_merge($page, [
                    'global_totals' => $globalTotals,
                    'include_global_totals' => $pageNumber === $pageTotal,
                ]),
                'personnel_snapshot_id' => $snapshotId,
                'roster_hash' => $rosterHash,
                'page_number' => $pageNumber,
                'page_total' => $pageTotal,
            ]);

            $results[] = $this->createJob(
                tenantId: $tenantId,
                branchId: $branchId,
                requestedByUserId: $requestedByUserId,
                cashSessionId: $cashSessionId,
                idempotencyKey: $this->personnelPageKey($cashSessionId, $pageNumber, $pageTotal, $rosterHash, false),
                payload: $pagePayload,
                contentText: $this->contentBuilder->buildCashPersonnelTicket($pagePayload, printedAt: $printedAt),
            );
        }

        return $results;
    }

    /**
     * @return array{job: ?array<string, mixed>, jobs: list<array<string, mixed>>, summary_job: ?array<string, mixed>, personnel_job: ?array<string, mixed>, personnel_jobs: list<array<string, mixed>>, personnel_pages: int, warning: ?string, warnings: list<string>}
     */
    private function reprintPersonnelPages(
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        int $sessionId,
        ?string $idempotencyKey,
        ?int $page = null,
    ): array {
        $sourcePages = $this->loadLatestPersonnelSnapshotPages($tenantId, $branchId, $sessionId);
        if ($sourcePages === []) {
            return [
                'job' => null,
                'jobs' => [],
                'summary_job' => null,
                'personnel_job' => null,
                'personnel_jobs' => [],
                'personnel_pages' => 0,
                'warning' => 'No existe snapshot histórico para reimprimir pagos del personal.',
                'warnings' => ['No existe snapshot histórico para reimprimir pagos del personal.'],
            ];
        }

        if ($page !== null) {
            $sourcePages = array_values(array_filter($sourcePages, static fn (PrintJobModel $job): bool => (int) (($job->payload['page_number'] ?? 0)) === $page));
        }

        $created = [];
        foreach ($sourcePages as $source) {
            $payload = is_array($source->payload) ? $source->payload : [];
            $pageNumber = (int) ($payload['page_number'] ?? 1);
            $pageTotal = (int) ($payload['page_total'] ?? 1);
            $rosterHash = (string) ($payload['roster_hash'] ?? 'snapshot');

            $created[] = $this->createJob(
                tenantId: $tenantId,
                branchId: $branchId,
                requestedByUserId: $requestedByUserId,
                cashSessionId: $sessionId,
                idempotencyKey: $this->personnelPageKey($sessionId, $pageNumber, $pageTotal, $rosterHash, true),
                payload: $payload,
                contentText: (string) $source->content_text,
            );
        }

        $jobs = array_values(array_filter(array_map(static fn (array $result): ?array => $result['job'] ?? null, $created)));
        $warnings = array_values(array_filter(array_map(static fn (array $result): ?string => $result['warning'] ?? null, $created)));

        return [
            'job' => $jobs[0] ?? null,
            'jobs' => $jobs,
            'summary_job' => null,
            'personnel_job' => $jobs[0] ?? null,
            'personnel_jobs' => $jobs,
            'personnel_pages' => count($jobs),
            'warning' => $warnings[0] ?? null,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return list<PrintJobModel>
     */
    private function loadLatestPersonnelSnapshotPages(int $tenantId, int $branchId, int $sessionId): array
    {
        $latest = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('source_type', PrintJobSourceType::CashSession->value)
            ->where('source_id', $sessionId)
            ->where('type', PrintJobType::CashClose->value)
            ->where('payload->ticket_key', 'personnel')
            ->whereNotNull('payload->personnel_snapshot_id')
            ->orderByDesc('id')
            ->first();

        if ($latest === null) {
            return [];
        }

        $snapshotId = (string) (($latest->payload['personnel_snapshot_id'] ?? ''));
        if ($snapshotId === '') {
            return [];
        }

        return PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('source_type', PrintJobSourceType::CashSession->value)
            ->where('source_id', $sessionId)
            ->where('type', PrintJobType::CashClose->value)
            ->where('payload->ticket_key', 'personnel')
            ->where('payload->personnel_snapshot_id', $snapshotId)
            ->orderByRaw("JSON_EXTRACT(payload, '$.page_number') ASC")
            ->orderBy('created_at')
            ->get()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<array<string, mixed>>
     */
    private function personnelEntries(array $payload): array
    {
        $summary = $payload['financial_dashboard']['settlement_summary']['totals']['personnel_rows'] ?? [];
        $roles = [
            'GIRL' => $summary['girls'] ?? [],
            'WAITER' => $summary['waiters'] ?? [],
            'CLEANING' => $summary['cleaning'] ?? [],
        ];

        $entries = [];
        foreach ($roles as $role => $rows) {
            usort($rows, static fn (array $a, array $b): int => strcasecmp((string) ($a['staff_name'] ?? ''), (string) ($b['staff_name'] ?? '')));
            $index = 1;
            foreach ($rows as $row) {
                $entries[] = [
                    'role' => $role,
                    'index' => $index,
                    'name' => (string) ($row['staff_name'] ?? '—'),
                    'sale' => (string) ($row['sales_total_amount'] ?? '0.00'),
                    'pay' => (string) ($row['total_amount'] ?? '0.00'),
                ];
                $index++;
            }
        }

        return $entries;
    }

    /**
     * @param list<array<string, mixed>> $entries
     * @return list<array<string, mixed>>
     */
    private function paginatePersonnelEntries(array $entries, int $paperWidthMm): array
    {
        $maxLines = $paperWidthMm <= 58
            ? 36
            : (int) config('nightpos.printing.max_lines_per_personnel_ticket_80mm', 58);
        $headerLines = 8;
        $tailLines = 11;

        $pages = [];
        $index = 0;
        $totalEntries = count($entries);

        while ($index < $totalEntries) {
            $used = $headerLines + $tailLines;
            $pageEntries = [];
            $rolesInPage = [];

            while ($index < $totalEntries) {
                $entry = $entries[$index];
                $role = (string) ($entry['role'] ?? '');
                $entryLines = 1 + (! in_array($role, $rolesInPage, true) ? 3 : 0);

                if (($used + $entryLines > $maxLines) && $pageEntries !== []) {
                    break;
                }

                $pageEntries[] = $entry;
                $used += $entryLines;
                if (! in_array($role, $rolesInPage, true)) {
                    $rolesInPage[] = $role;
                }
                $index++;
            }

            $pages[] = ['entries' => $pageEntries];
        }

        $globalIndex = 1;
        $totalPages = count($pages);
        foreach ($pages as $pageIndex => $page) {
            $count = count($page['entries']);
            $pages[$pageIndex]['page_number'] = $pageIndex + 1;
            $pages[$pageIndex]['page_total'] = $totalPages;
            $pages[$pageIndex]['range_start'] = $count > 0 ? $globalIndex : 0;
            $pages[$pageIndex]['range_end'] = $count > 0 ? ($globalIndex + $count - 1) : 0;
            $pages[$pageIndex]['total_records'] = $totalEntries;
            $pages[$pageIndex]['page_subtotal'] = number_format(array_reduce(
                $page['entries'],
                static fn (float $acc, array $entry): float => $acc + (float) ($entry['pay'] ?? 0),
                0.0,
            ), 2, '.', '');
            $globalIndex += $count;
        }

        return $pages;
    }

    /**
     * @param list<array<string, mixed>> $entries
     * @return array{girls: string, waiters: string, cleaning: string, personnel: string}
     */
    private function personnelGlobalTotals(array $entries): array
    {
        $girls = 0.0;
        $waiters = 0.0;
        $cleaning = 0.0;

        foreach ($entries as $entry) {
            $pay = (float) ($entry['pay'] ?? 0);
            $role = (string) ($entry['role'] ?? '');
            if ($role === 'WAITER') {
                $waiters += $pay;
            } elseif ($role === 'CLEANING') {
                $cleaning += $pay;
            } else {
                $girls += $pay;
            }
        }

        $total = $girls + $waiters + $cleaning;

        return [
            'girls' => number_format($girls, 2, '.', ''),
            'waiters' => number_format($waiters, 2, '.', ''),
            'cleaning' => number_format($cleaning, 2, '.', ''),
            'personnel' => number_format($total, 2, '.', ''),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    private function createJob(
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        int $cashSessionId,
        string $idempotencyKey,
        array $payload,
        string $contentText,
    ): array {
        $this->assertIdempotencyKeyLength($idempotencyKey, $cashSessionId, $payload);

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $idempotencyKey);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        try {
            $job = $this->jobs->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'device_id' => null,
                'type' => PrintJobType::CashClose->value,
                'source_type' => PrintJobSourceType::CashSession->value,
                'source_id' => $cashSessionId,
                'idempotency_key' => $idempotencyKey,
                'payload' => $payload,
                'content_text' => $contentText,
                'status' => PrintJobStatus::Pending->value,
                'requested_by_user_id' => $requestedByUserId,
            ]);

            $this->eventEmitter->emit(
                $tenantId,
                $branchId,
                'print_job.created',
                [
                    'print_job_id' => $job['id'],
                    'cash_session_id' => $cashSessionId,
                    'type' => PrintJobType::CashClose->value,
                    'status' => PrintJobStatus::Pending->value,
                    'ticket_key' => $payload['ticket_key'] ?? null,
                ],
            );

            return ['job' => $job, 'warning' => null];
        } catch (\Throwable $exception) {
            Log::error('print_job.create_failed', [
                'cash_session_id' => $cashSessionId,
                'ticket_key' => $payload['ticket_key'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'idempotency_key_length' => strlen($idempotencyKey),
                'idempotency_key_hash' => substr(sha1($idempotencyKey), 0, 12),
                'error' => $exception->getMessage(),
            ]);

            return [
                'job' => null,
                'warning' => $payload['ticket_key'] === 'personnel'
                    ? 'Caja cerrada, pero no se pudo imprimir el ticket de personal.'
                    : 'Caja cerrada, pero no se pudo imprimir el resumen de cierre.',
            ];
        }
    }

    private function ticketKey(int $sessionId, string $ticketKey, ?string $idempotencyKey): string
    {
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            return $idempotencyKey.':'.$ticketKey;
        }

        return "cash_close:{$sessionId}:{$ticketKey}";
    }

    private function personnelPageKey(int $sessionId, int $page, int $total, string $rosterHash, bool $isReprint): string
    {
        $normalized = strtolower(trim($rosterHash));
        $stable = substr(sha1($normalized), 0, $isReprint ? 8 : 12);

        if ($isReprint) {
            return "cash_close:{$sessionId}:pers:{$page}:{$total}:re:{$stable}";
        }

        return "cash_close:{$sessionId}:pers:{$page}:{$total}:{$stable}";
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertIdempotencyKeyLength(string $idempotencyKey, int $cashSessionId, array $payload): void
    {
        $length = strlen($idempotencyKey);
        if ($length <= 64) {
            return;
        }

        $preview = substr($idempotencyKey, 0, 32);
        $hash = substr(sha1($idempotencyKey), 0, 12);

        Log::error('print_job.idempotency_key_too_long', [
            'cash_session_id' => $cashSessionId,
            'ticket_key' => $payload['ticket_key'] ?? null,
            'idempotency_key_length' => $length,
            'idempotency_key_preview' => $preview,
            'idempotency_key_hash' => $hash,
        ]);

        throw new InvalidArgumentException("print_job idempotency_key length {$length} exceeds 64 (hash {$hash})");
    }
}
