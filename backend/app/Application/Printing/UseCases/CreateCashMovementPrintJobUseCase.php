<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Cash\Services\CashPrintPresenter;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateCashMovementPrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        int $movementId,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?string $idempotencyKey = null,
    ): array {
        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return [
                'job' => null,
                'warning' => 'Movimiento registrado, pero no se pudo imprimir el comprobante (sin impresora activa).',
            ];
        }

        $presented = CashPrintPresenter::movement($movementId, $tenantId);

        if ($presented === null || ! isset($presented['movement'])) {
            return [
                'job' => null,
                'warning' => 'Movimiento registrado, pero no se pudo imprimir el comprobante.',
            ];
        }

        $key = $idempotencyKey ?? "cash_movement:{$movementId}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        $printedAt = now()->toIso8601String();
        $payload = array_merge($presented, ['printed_at' => $printedAt]);

        $contentText = $this->contentBuilder->buildCashMovement(
            $presented['movement'],
            $presented['branch_name'] ?? null,
            $presented['cashier_name'] ?? null,
            printedAt: $printedAt,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::CashMovement->value,
            'source_type' => PrintJobSourceType::CashMovement->value,
            'source_id' => $movementId,
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
                'cash_movement_id' => $movementId,
                'type' => PrintJobType::CashMovement->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
