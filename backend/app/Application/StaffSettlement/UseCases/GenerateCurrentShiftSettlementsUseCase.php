<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\StaffSettlement\Services\SettlementAccessPolicy;
use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;
use App\Application\StaffSettlement\Support\SettlementOperationalContextBuilder;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GenerateCurrentShiftSettlementsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementOperationalContextBuilder $contextBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw StaffSettlementDomainException::shiftRequired();
        }

        if (! $this->staffContext->hasPermission('settlements.generate')) {
            throw PermissionDeniedException::forPermission('settlements.generate');
        }

        $userId = $this->staffContext->userId();

        if ($userId === null) {
            throw StaffSettlementDomainException::shiftRequired();
        }

        $cashSession = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $userId);
        $scope = SettlementShiftScopeResolver::SCOPE_SHIFT;
        $cashSessionId = null;
        $cashSessionShiftId = null;

        if (
            $this->staffContext->hasPermission('cash.access')
            && (
                $this->staffContext->roleSlug() === 'cashier'
                || $this->staffContext->staffRole() === 'CASHIER'
            )
            && ! $this->staffContext->hasPermission('admin.cash_sessions.view')
            && $cashSession?->officialShiftId !== null
        ) {
            $shiftId = $cashSession->officialShiftId;
            $scope = SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION;
            $cashSessionId = $cashSession->id;
            $cashSessionShiftId = $cashSession->officialShiftId;
        }
        else {
            $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);
            $shiftId = $shift->id;
            $cashSessionId = $cashSession?->id;
            $cashSessionShiftId = $cashSession?->officialShiftId;
        }

        $result = $this->settlements->generateForShift($tenant->id, $branch->id, $shiftId);
        $openShiftId = $this->settlements->resolveOpenShiftId($tenant->id, $branch->id);

        $operational = $this->contextBuilder->build(
            $this->settlements,
            $tenant->id,
            $branch->id,
            $shiftId,
            $cashSessionId,
            $userId,
            $scope,
            $openShiftId,
            $cashSessionShiftId,
            $cashSessionShiftId !== null && $openShiftId !== null && $cashSessionShiftId !== $openShiftId,
            false,
        );

        $message = $result['created_items'] > 0
            ? 'Liquidaciones generadas para el turno actual.'
            : (($operational['settlement_summary']['generated_pending_count'] ?? 0) > 0
                ? 'No hay nuevas liquidaciones para generar. Ya existen pagos pendientes.'
                : 'No hay liquidaciones nuevas para generar en este turno/caja.');

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'settlement.generated',
            [
                'entity'  => ['type' => 'shift', 'id' => $shiftId],
                'summary' => 'Liquidaciones generadas para el turno',
                'refresh' => ['settlements'],
            ]
        );

        return OperationResult::ok($message, array_merge($result, $operational));
    }
}
