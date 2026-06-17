<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashSessionCloseCheckBuilder;
use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\StaffSettlement\Support\SettlementOperationalContextBuilder;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashSessionCloseCheckUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly CashSessionCloseCheckBuilder $closeCheckBuilder,
        private readonly SettlementOperationalContextBuilder $contextBuilder,
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        $session = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $userId);

        if ($session === null) {
            return OperationResult::ok('No hay sesión de caja abierta.', [
                'can_close' => false,
                'blockers'  => [[
                    'type'    => 'NO_OPEN_SESSION',
                    'code'    => 'no_open_session',
                    'count'   => 0,
                    'message' => 'No hay caja abierta.',
                ]],
                'warnings' => [],
                'actions'  => [],
                'summary'  => [],
            ]);
        }

        $shiftId = $session->officialShiftId;

        if ($shiftId === null) {
            return OperationResult::ok('La caja no tiene turno asociado.', [
                'can_close' => false,
                'blockers'  => [[
                    'type'    => 'NO_SHIFT_ON_SESSION',
                    'code'    => 'no_shift_on_session',
                    'count'   => 0,
                    'message' => 'La sesión de caja no tiene turno oficial asociado.',
                ]],
                'warnings' => [],
                'actions'  => [],
                'summary'  => [],
                'cash_session_id' => $session->id,
            ]);
        }

        $check = $this->closeCheckBuilder->build($tenant->id, $branch->id, $shiftId, $session->id);
        $operational = $this->contextBuilder->build(
            $this->settlements,
            $tenant->id,
            $branch->id,
            $shiftId,
            $session->id,
            $userId,
            'my_cash_session',
            $this->settlements->resolveOpenShiftId($tenant->id, $branch->id),
            $shiftId,
            false,
            false,
        );

        return OperationResult::ok('Verificación de cierre de caja.', array_merge($check, $operational, [
            'cash_session_id' => $session->id,
            'official_shift_id' => $shiftId,
            'auth_user_id' => $userId,
            'role' => $this->staffContext->roleSlug(),
        ]));
    }
}
