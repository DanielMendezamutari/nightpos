<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\Reports\Services\ComboBraceletReportingService;
use App\Application\StaffSettlement\Services\SettlementAccessPolicy;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Auth;

final class GetSettlementUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementAccessPolicy $accessPolicy,
        private readonly ComboBraceletReportingService $comboReporting,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.access')) {
            throw PermissionDeniedException::forPermission('settlements.access');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $settlementId = (int) ($input->settlementId ?? 0);

        if ($tenant === null || $branch === null || $settlementId <= 0) {
            throw new StaffSettlementNotFoundException();
        }

        $detail = $this->settlements->findById(
            $settlementId,
            $tenant->id,
            $branch->id,
            $this->resolveStaffScopeUserId(),
        );

        if ($detail === null) {
            throw new StaffSettlementNotFoundException();
        }

        return OperationResult::ok('Detalle de liquidación.', [
            'settlement' => SettlementMapper::settlement($detail['settlement']),
            'items' => array_map(
                fn (array $row) => SettlementMapper::item(
                    $this->comboReporting->enrichSettlementItem($row),
                ),
                $detail['items'],
            ),
            'adjustments' => array_map(
                static fn (array $row) => SettlementMapper::adjustment($row),
                $detail['adjustments'] ?? [],
            ),
        ]);
    }

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
}
