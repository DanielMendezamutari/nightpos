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
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

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

        $key = $idempotencyKey ?? "cash_close:{$session->id}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        $printedAt = now()->toIso8601String();
        $payload = $this->payloadEnricher->enrich(
            array_merge($presented, ['printed_at' => $printedAt]),
            $tenantId,
            $branchId,
        );

        $contentText = $this->contentBuilder->buildCashClose($payload, printedAt: $printedAt);

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::CashClose->value,
            'source_type' => PrintJobSourceType::CashSession->value,
            'source_id' => $session->id,
            'idempotency_key' => $key,
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
                'cash_session_id' => $session->id,
                'type' => PrintJobType::CashClose->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
