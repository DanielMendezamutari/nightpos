<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Cash\Services\CashPrintPresenter;
use App\Application\Printing\Services\ShiftClosePrintPayloadEnricher;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateShiftClosePrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly ShiftClosePrintPayloadEnricher $payloadEnricher,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        int $shiftId,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?string $idempotencyKey = null,
    ): array {
        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return [
                'job' => null,
                'warning' => 'No hay impresora activa. Puede usar la vista imprimible del navegador.',
            ];
        }

        $shift = $this->shifts->findById($shiftId, $tenantId);

        if ($shift === null || $shift->branchId !== $branchId) {
            return [
                'job' => null,
                'warning' => 'No se pudo imprimir el cierre de turno.',
            ];
        }

        $closure = $this->shifts->findClosureByShiftId($shiftId, $tenantId);

        if ($closure === null) {
            return [
                'job' => null,
                'warning' => 'El turno aún no tiene cierre registrado.',
            ];
        }

        $totals = $this->shifts->buildSummaryTotals($shiftId, $tenantId, $branchId);
        $summary = array_merge($totals, [
            'counted_cash' => $closure->countedCash,
            'cash_difference' => $closure->cashDifference,
            'total_waiter_payouts' => $closure->totalWaiterPayouts,
            'total_girl_payouts' => $closure->totalGirlPayouts,
        ]);

        $presented = CashPrintPresenter::shiftClose($shift, $closure, $summary);

        if ($presented === null) {
            return [
                'job' => null,
                'warning' => 'No se pudo imprimir el cierre de turno.',
            ];
        }

        $key = $idempotencyKey ?? "shift_close:{$shiftId}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null && ! str_contains($key, 'reprint:')) {
            return ['job' => $existing, 'warning' => null];
        }

        $printedAt = now()->toIso8601String();
        $payload = $this->payloadEnricher->enrich(
            array_merge($presented, ['printed_at' => $printedAt]),
            $tenantId,
            $branchId,
            $shiftId,
        );

        $contentText = $this->contentBuilder->buildShiftClose($payload, printedAt: $printedAt);

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::ShiftClose->value,
            'source_type' => PrintJobSourceType::Shift->value,
            'source_id' => $shiftId,
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
                'official_shift_id' => $shiftId,
                'type' => PrintJobType::ShiftClose->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
