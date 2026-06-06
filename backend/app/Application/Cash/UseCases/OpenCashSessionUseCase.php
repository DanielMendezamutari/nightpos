<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\OpenCashSessionInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Cash\Support\CashMapper;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class OpenCashSessionUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $sessions,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof OpenCashSessionInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        if ((float) $input->openingAmount < 0) {
            throw CashDomainException::invalidOpeningAmount();
        }

        $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);

        $existing = $this->sessions->findOpenForUser($tenant->id, $branch->id, $userId);

        if ($existing !== null) {
            throw CashDomainException::sessionAlreadyOpen();
        }

        $session = $this->sessions->open(
            tenantId: $tenant->id,
            branchId: $branch->id,
            officialShiftId: $shift->id,
            cashRegisterId: $input->cashRegisterId,
            openedByUserId: $userId,
            openingAmount: $input->openingAmount,
            openingNotes: $input->openingNotes,
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'cash.session.opened',
            [
                'entity'  => ['type' => 'cash_session', 'id' => $session->id],
                'summary' => 'Caja abierta',
                'refresh' => ['cash'],
            ]
        );

        return OperationResult::ok('Caja abierta correctamente.', [
            'session' => CashMapper::session($session),
        ]);
    }
}
