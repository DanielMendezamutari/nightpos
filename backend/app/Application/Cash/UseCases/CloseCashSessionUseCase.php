<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\CloseCashSessionInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Cash\Services\CashSessionCloseCheckBuilder;
use App\Application\Cash\Support\CashMapper;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CloseCashSessionUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $sessions,
        private readonly CashSessionCloseCheckBuilder $closeCheckBuilder,
        private readonly AuditLogRecorder $audit,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CloseCashSessionInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        if ((float) $input->declaredClosingAmount < 0) {
            throw CashDomainException::invalidAmount();
        }

        $session = $this->sessions->findOpenForUser($tenant->id, $branch->id, $userId);

        if ($session === null) {
            throw CashDomainException::noOpenSession();
        }

        $shiftId = $session->officialShiftId;

        if ($shiftId === null) {
            throw CashDomainException::cannotCloseWithBlockers('La sesión de caja no tiene turno oficial asociado.');
        }

        $check = $this->closeCheckBuilder->build($tenant->id, $branch->id, $shiftId);

        if (! $check['can_close']) {
            $messages = array_map(static fn (array $b) => $b['message'], $check['blockers']);
            throw CashDomainException::cannotCloseWithBlockers(implode(' ', $messages));
        }

        $closed = $this->sessions->close(
            sessionId: $session->id,
            tenantId: $tenant->id,
            closedByUserId: $userId,
            declaredClosingAmount: $input->declaredClosingAmount,
            closingNotes: $input->closingNotes,
        );

        $this->audit->record(
            'cash_session.closed',
            'cash_session',
            $closed->id,
            [
                'declared_closing_amount' => $input->declaredClosingAmount,
                'expected_amount' => $closed->expectedAmount,
            ],
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'cash.session.closed',
            [
                'entity'  => ['type' => 'cash_session', 'id' => $closed->id],
                'summary' => 'Caja cerrada',
                'refresh' => ['cash', 'shift_console'],
            ]
        );

        return OperationResult::ok('Caja cerrada correctamente.', [
            'session' => CashMapper::session($closed),
        ]);
    }
}
