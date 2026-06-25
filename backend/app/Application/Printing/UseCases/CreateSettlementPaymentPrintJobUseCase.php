<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\StaffSettlement\Services\SettlementPrintPresenter;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateSettlementPaymentPrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly SettlementPrintPresenter $presenter,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        int $settlementId,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        bool $isReprint = false,
        ?int $reprintNumber = null,
        ?string $reprintedByName = null,
        ?string $idempotencyKey = null,
    ): array {
        $presented = $this->presenter->payment(
            settlementId: $settlementId,
            tenantId: $tenantId,
            isReprint: $isReprint,
            reprintNumber: $reprintNumber,
            reprintedByName: $reprintedByName,
            reprintedAt: now()->format('Y-m-d H:i:s'),
        );

        if ($presented === null) {
            return [
                'job' => null,
                'warning' => 'Liquidación pagada, pero no se pudo imprimir el comprobante.',
            ];
        }

        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            $this->audit->record(
                'SETTLEMENT_PRINT_FAILED',
                'staff_settlement',
                $settlementId,
                ['reason' => 'no_active_device', 'is_reprint' => $isReprint],
            );

            return [
                'job' => null,
                'warning' => 'Liquidación pagada, pero no se pudo imprimir el comprobante (sin impresora activa).',
            ];
        }

        $key = $idempotencyKey ?? ($isReprint
            ? "settlement_payment:{$settlementId}:reprint:".now()->timestamp
            : "settlement_payment:{$settlementId}:v1");

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        $printedAt = now()->toIso8601String();
        $payload = array_merge($presented, ['printed_at' => $printedAt]);

        $contentText = $this->contentBuilder->buildSettlementPayment(
            $payload,
            printedAt: $printedAt,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::SettlementPayment->value,
            'source_type' => PrintJobSourceType::StaffSettlement->value,
            'source_id' => $settlementId,
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
                'settlement_id' => $settlementId,
                'type' => PrintJobType::SettlementPayment->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
