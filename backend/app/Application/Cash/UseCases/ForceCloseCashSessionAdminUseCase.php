<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\ForceCloseCashSessionAdminInput;
use App\Application\Cash\Services\CashSessionCloseCheckBuilder;
use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Cash\Support\AdminCashSessionMapper;
use App\Application\Cash\Support\CashMapper;
use App\Application\Printing\UseCases\CreateCashClosePrintJobUseCase;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ForceCloseCashSessionAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly CashSessionCloseCheckBuilder $closeCheckBuilder,
        private readonly CashSessionFinancialSummaryBuilder $financials,
        private readonly AuditLogRecorder $audit,
        private readonly CreateCashClosePrintJobUseCase $createCashClosePrintJob,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof ForceCloseCashSessionAdminInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! $this->staffContext->hasPermission('admin.cash_sessions.force_close')) {
            throw PermissionDeniedException::forPermission('admin.cash_sessions.force_close');
        }

        $tenant = $this->tenantContext->tenant();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        $model = $this->assertAccessibleSession($input->sessionId, $tenant->id);

        if ($model->status !== CashSessionStatus::OPEN) {
            throw CashDomainException::sessionClosed();
        }

        $shiftId = $model->official_shift_id;

        if ($shiftId === null) {
            throw CashDomainException::cannotCloseWithBlockers('La sesión de caja no tiene turno oficial asociado.');
        }

        $blockersSnapshot = $this->closeCheckBuilder->build(
            $tenant->id,
            (int) $model->branch_id,
            (int) $shiftId,
            (int) $model->id,
        );

        $financialSnapshot = $this->financials->build(
            sessionId: (int) $model->id,
            openingAmount: (string) $model->opening_amount,
            storedExpectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
            declaredClosingAmount: null,
            differenceAmount: null,
            status: $model->status,
        );

        $expectedAmount = (string) ($financialSnapshot['expected_cash'] ?? '0.00');

        $closed = $this->cashSessions->forceClose(
            sessionId: (int) $model->id,
            tenantId: $tenant->id,
            forcedClosedByUserId: $userId,
            expectedAmount: $expectedAmount,
            forcedCloseReason: $input->forcedCloseReason,
            forcedCloseNotes: trim($input->forcedCloseNotes),
            closeBlockersSnapshot: $blockersSnapshot,
            financialSummarySnapshot: $financialSnapshot,
        );

        $this->audit->record(
            'cash_session.force_closed',
            'cash_session',
            $closed->id,
            [
                'forced_close_reason' => $input->forcedCloseReason,
                'forced_close_notes' => trim($input->forcedCloseNotes),
                'opened_by_user_id' => (int) $model->opened_by_user_id,
                'forced_by_user_id' => $userId,
                'expected_amount' => $expectedAmount,
                'blockers_count' => count($blockersSnapshot['blockers'] ?? []),
            ],
        );

        $this->eventEmitter->emit(
            $tenant->id,
            (int) $model->branch_id,
            'cash.session.closed',
            [
                'entity' => ['type' => 'cash_session', 'id' => $closed->id],
                'summary' => 'Caja cerrada administrativamente',
                'forced' => true,
                'opened_by_user_id' => (int) $model->opened_by_user_id,
                'forced_by_user_id' => $userId,
                'refresh' => ['cash', 'shift_console'],
            ],
        );

        $closedModel = $this->cashSessions->findModelForAdmin($closed->id, $tenant->id);

        $responseSession = $closedModel !== null
            ? array_merge(
                AdminCashSessionMapper::listItem($closedModel, $financialSnapshot),
                AdminCashSessionMapper::forceCloseMeta($closedModel),
            )
            : CashMapper::session($closed);

        $printResult = $this->createCashClosePrintJob->execute(
            session: $closed,
            tenantId: $tenant->id,
            branchId: (int) $model->branch_id,
            requestedByUserId: $userId,
        );

        return OperationResult::ok('Caja cerrada administrativamente.', [
            'session' => $responseSession,
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }

    private function assertAccessibleSession(int $sessionId, int $tenantId): CashSessionModel
    {
        $model = $this->cashSessions->findModelForAdmin($sessionId, $tenantId);

        if ($model === null) {
            throw new CashSessionNotFoundException();
        }

        $branch = $this->branchContext->branch();

        if ($branch === null) {
            throw CashDomainException::branchRequired();
        }

        if (! $this->staffContext->isSuperAdmin() && (int) $model->branch_id !== $branch->id) {
            throw BranchAccessDeniedException::create();
        }

        return $model;
    }
}
