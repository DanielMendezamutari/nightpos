<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\RegisterCashMovementInput;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\Cash\Support\CashMapper;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashMovementType;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class RegisterCashMovementUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly CashSessionRepositoryInterface $sessions,
        private readonly CashMovementReasonRepositoryInterface $reasons,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof RegisterCashMovementInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        if ((float) $input->amount <= 0) {
            throw CashDomainException::invalidAmount();
        }

        $session = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $userId);

        if ($session === null) {
            throw CashDomainException::noOpenSession();
        }

        $type = CashMovementType::fromString($input->movementType);

        $reason = $this->reasons->findById($input->cashMovementReasonId, $tenant->id);

        if ($reason === null || $reason['status'] !== 'active') {
            throw MasterDataDomainException::notFound();
        }

        if ($reason['type'] !== $type->value) {
            throw MasterDataDomainException::invalidReasonType();
        }

        $description = $reason['name'];
        if ($input->notes !== null && trim($input->notes) !== '') {
            $description = $reason['name'].' — '.trim($input->notes);
        }

        $this->sessions->addMovement(
            tenantId: $tenant->id,
            branchId: $branch->id,
            cashSessionId: $session->id,
            movementType: $type->value,
            amount: $input->amount,
            description: $description,
            paymentMethod: $input->paymentMethod,
            createdByUserId: $userId,
            cashMovementReasonId: $input->cashMovementReasonId,
            notes: $input->notes,
        );

        $updated = $this->sessions->findById($session->id, $tenant->id);

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'cash.movement.created',
            [
                'entity'  => ['type' => 'cash_session', 'id' => $session->id],
                'summary' => 'Movimiento de caja registrado',
                'refresh' => ['cash'],
            ]
        );

        return OperationResult::ok('Movimiento registrado.', [
            'session' => CashMapper::session($updated),
        ]);
    }
}
