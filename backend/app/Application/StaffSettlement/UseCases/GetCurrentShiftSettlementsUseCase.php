<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementAccessPolicy;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Auth;

final class GetCurrentShiftSettlementsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementAccessPolicy $accessPolicy,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.access')) {
            throw PermissionDeniedException::forPermission('settlements.access');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw StaffSettlementDomainException::shiftRequired();
        }

        $shiftId = $this->settlements->resolveOverviewShiftId($tenant->id, $branch->id);

        if ($shiftId === null) {
            return OperationResult::ok('Sin turno para liquidaciones.', [
                'shift' => null,
                'summary' => $this->emptySummary(),
                'waiters' => [],
                'girls' => [],
                'settlements' => [],
            ]);
        }

        $overview = $this->settlements->getCurrentShiftOverview(
            $tenant->id,
            $branch->id,
            $shiftId,
            $this->resolveStaffScopeUserId(),
        );

        return OperationResult::ok('Liquidaciones del turno actual.', $overview);
    }

    /**
     * @return array<string, string>
     */
    private function resolveStaffScopeUserId(): ?int
    {
        $scoped = $this->accessPolicy->scopedStaffUserId();

        if ($scoped !== null) {
            return $scoped;
        }

        if ($this->staffContext->isSuperAdmin()
            || $this->staffContext->hasPermission('settlements.generate')
            || $this->staffContext->hasPermission('settlements.pay')
            || $this->staffContext->hasPermission('settlements.history')) {
            return null;
        }

        $userId = $this->staffContext->userId() ?? Auth::id();

        return $userId !== null ? (int) $userId : null;
    }

    /**
     * @return array<string, string>
     */
    private function emptySummary(): array
    {
        return [
            'total_waiters' => '0.00',
            'total_girls' => '0.00',
            'total_cleaning' => '0.00',
            'total_consumption' => '0.00',
            'total_bracelets' => '0.00',
            'total_pieces' => '0.00',
            'total_shows' => '0.00',
            'total_pending' => '0.00',
            'total_paid' => '0.00',
        ];
    }
}
