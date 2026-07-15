<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\UseCaseInterface;

final class SyncSettlementsFromShowUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly OperationalEventEmitter $eventEmitter,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenantId = (int) ($input->tenantId ?? 0);
        $branchId = (int) ($input->branchId ?? 0);
        $showId = (int) ($input->showId ?? 0);
        $cashSessionId = isset($input->cashSessionId) ? (int) $input->cashSessionId : null;

        if ($tenantId <= 0 || $branchId <= 0 || $showId <= 0) {
            return OperationResult::fail('Parámetros inválidos para sincronizar liquidaciones de show.');
        }

        try {
            $result = $this->settlements->syncFromShow($tenantId, $branchId, $showId);

            $this->eventEmitter->emit(
                $tenantId,
                $branchId,
                'settlement.generated',
                [
                    'entity' => ['type' => 'show', 'id' => $showId],
                    'summary' => 'Liquidaciones sincronizadas automáticamente',
                    'refresh' => ['settlements', 'cash'],
                ]
            );

            return OperationResult::ok('Liquidaciones sincronizadas automáticamente.', $result);
        }
        catch (\Throwable $exception) {
            report($exception);

            $this->audit->record(
                'settlement.auto_sync_failed',
                'show',
                $showId,
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'cash_session_id' => $cashSessionId,
                    'error' => $exception->getMessage(),
                ],
            );

            $this->eventEmitter->emit(
                $tenantId,
                $branchId,
                'settlement.sync_delayed',
                [
                    'entity' => ['type' => 'show', 'id' => $showId],
                    'summary' => 'Sincronización de liquidaciones demorada',
                    'refresh' => ['settlements', 'cash'],
                ]
            );

            return OperationResult::fail('Algunos pagos podrían tardar en actualizarse. Reintente en unos segundos.');
        }
    }
}
