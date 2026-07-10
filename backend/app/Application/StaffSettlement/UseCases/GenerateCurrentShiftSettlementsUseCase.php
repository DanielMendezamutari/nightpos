<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\Cash\Services\OpenCashSessionResolver;
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
        private readonly SettlementShiftScopeResolver $scopeResolver,
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
        $scopeInfo = $this->scopeResolver->resolve($tenant->id, $branch->id, $userId);
        $scope = $scopeInfo['scope'];
        $cashSessionId = $scopeInfo['cash_session_id'];
        $cashSessionShiftId = $scopeInfo['cash_session_shift_id'];

        if ($scope === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION) {
            if ($cashSession === null || $scopeInfo['shift_id'] === null || $cashSessionId === null) {
                throw StaffSettlementDomainException::cashRequiredForGeneration();
            }

            $shiftIds = $this->settlements->resolveCashSessionShiftIdsWithActivity($tenant->id, $branch->id, $cashSessionId);

            if ($shiftIds === []) {
                $shiftIds = [(int) $scopeInfo['shift_id']];
            }

            $createdItems = 0;
            $settlementsTouched = 0;

            foreach ($shiftIds as $candidateShiftId) {
                $partial = $this->settlements->generateForShift($tenant->id, $branch->id, (int) $candidateShiftId, $cashSessionId);
                $createdItems += (int) ($partial['created_items'] ?? 0);
                $settlementsTouched += (int) ($partial['settlements_touched'] ?? 0);
            }

            $shiftId = (int) end($shiftIds);
            $result = [
                'created_items' => $createdItems,
                'settlements_touched' => $settlementsTouched,
                'shift_id' => $shiftId,
                'generated_shift_ids' => $shiftIds,
            ];
        }
        else {
            $shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $userId);
            $shiftId = $shift->id;
            $cashSessionId = $cashSession?->id ?? $cashSessionId;
            $cashSessionShiftId = $cashSession?->officialShiftId ?? $cashSessionShiftId;
            $result = $this->settlements->generateForShift($tenant->id, $branch->id, $shiftId);
        }

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
            $scopeInfo['shift_rotated'],
            $scopeInfo['empty_overview'] && $result['created_items'] === 0,
        );

        $message = $result['created_items'] > 0
            ? ($scope === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION
                ? 'Liquidaciones generadas para su caja actual.'
                : 'Liquidaciones generadas para el turno actual.')
            : (($operational['settlement_summary']['generated_pending_count'] ?? 0) > 0
                ? 'No hay nuevas liquidaciones para generar. Ya existen pagos pendientes.'
                : ($scope === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION
                    ? 'No hay liquidaciones nuevas para generar en su caja actual.'
                    : 'No hay liquidaciones nuevas para generar en este turno.'));

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
