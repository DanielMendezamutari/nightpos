<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;

/**
 * Resuelve turno oficial y alcance de liquidaciones según rol.
 *
 * - Cajera normal → scope "my_cash_session": turno de SU caja abierta.
 *   Muestra liquidaciones PENDING/unsettled de su caja aunque no haya ventas nuevas.
 *
 * - Admin / cajera senior → scope "shift": turno OPEN de la sucursal.
 */
final class SettlementShiftScopeResolver
{
    public const SCOPE_SHIFT = 'shift';

    public const SCOPE_MY_CASH_SESSION = 'my_cash_session';

    private const FULL_SCOPE_PERMISSION = 'admin.cash_sessions.view';

    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
        private readonly StaffSettlementRepositoryInterface $settlements,
    ) {
    }

    /**
     * @return array{
     *     shift_id: ?int,
     *     scope: string,
     *     cash_session_id: ?int,
     *     empty_overview: bool,
     *     shift_rotated: bool,
     *     cash_session_shift_id: ?int
     * }
     */
    public function resolve(int $tenantId, int $branchId, ?int $userId, ?string $requestedScope = null): array
    {
        if ($userId !== null) {
            $this->ensureOperationalShift->rotateStaleOpenShiftIfNeeded($tenantId, $branchId, $userId);
        }

        $openShiftId = $this->settlements->resolveOpenShiftId($tenantId, $branchId);

        $cashSession = $userId !== null
            ? $this->cashSessionResolver->findOpenForCurrentUser($tenantId, $branchId, $userId)
            : null;

        $scope = $this->effectiveScope($requestedScope);

        if ($scope === self::SCOPE_SHIFT) {
            return [
                'shift_id' => $openShiftId,
                'scope' => self::SCOPE_SHIFT,
                'cash_session_id' => $cashSession?->id,
                'empty_overview' => false,
                'shift_rotated' => false,
                'cash_session_shift_id' => $cashSession?->officialShiftId,
            ];
        }

        if ($cashSession === null) {
            return [
                'shift_id' => null,
                'scope' => self::SCOPE_MY_CASH_SESSION,
                'cash_session_id' => null,
                'empty_overview' => true,
                'shift_rotated' => false,
                'cash_session_shift_id' => null,
            ];
        }

        $sessionShiftId = $cashSession->officialShiftId;
        $shiftRotated = $sessionShiftId !== null
            && $openShiftId !== null
            && $sessionShiftId !== $openShiftId;

        if ($sessionShiftId === null) {
            return [
                'shift_id' => null,
                'scope' => self::SCOPE_MY_CASH_SESSION,
                'cash_session_id' => $cashSession->id,
                'empty_overview' => true,
                'shift_rotated' => false,
                'cash_session_shift_id' => null,
            ];
        }

        $hasActivity = $this->settlements->cashSessionHasActivity(
            $tenantId,
            $branchId,
            $sessionShiftId,
            $cashSession->id,
        );

        $pendingCount = $this->settlements->countPendingSettlements(
            $tenantId,
            $branchId,
            $sessionShiftId,
            $cashSession->id,
        );

        $unsettledCount = $this->settlements->countUnsettledShiftSources(
            $tenantId,
            $branchId,
            $sessionShiftId,
            $cashSession->id,
        );

        $hasSettlementWork = $pendingCount > 0 || $unsettledCount > 0;

        return [
            'shift_id' => $sessionShiftId,
            'scope' => self::SCOPE_MY_CASH_SESSION,
            'cash_session_id' => $cashSession->id,
            'empty_overview' => ! $hasActivity && ! $hasSettlementWork,
            'shift_rotated' => $shiftRotated,
            'cash_session_shift_id' => $sessionShiftId,
        ];
    }

    public function canUseFullScope(): bool
    {
        return $this->staffContext->isSuperAdmin()
            || $this->staffContext->hasPermission(self::FULL_SCOPE_PERMISSION);
    }

    private function effectiveScope(?string $requestedScope): string
    {
        if ($this->isMyCashSessionOperator()) {
            return self::SCOPE_MY_CASH_SESSION;
        }

        if ($this->canUseFullScope()) {
            return $requestedScope === self::SCOPE_MY_CASH_SESSION
                ? self::SCOPE_MY_CASH_SESSION
                : self::SCOPE_SHIFT;
        }

        return self::SCOPE_SHIFT;
    }

    private function isMyCashSessionOperator(): bool
    {
        if (! $this->staffContext->hasPermission('cash.access')) {
            return false;
        }

        if ($this->staffContext->hasPermission('admin.cash_sessions.view')) {
            return false;
        }

        return $this->staffContext->roleSlug() === 'cashier'
            || $this->staffContext->staffRole() === 'CASHIER';
    }
}
