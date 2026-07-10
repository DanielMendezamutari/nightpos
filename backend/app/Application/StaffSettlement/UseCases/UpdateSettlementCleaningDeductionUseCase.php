<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementManualCleaningService;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateSettlementCleaningDeductionUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementManualCleaningService $cleaning,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.pay')) {
            throw PermissionDeniedException::forPermission('settlements.pay');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        $settlementId = (int) ($input->settlementId ?? 0);
        $amount = round((float) ($input->amount ?? -1), 2);

        if ($tenant === null || $branch === null || $userId === null || $settlementId <= 0) {
            throw new StaffSettlementNotFoundException();
        }

        $model = StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->first();

        if ($model === null) {
            throw new StaffSettlementNotFoundException();
        }

        $before = [
            'adjustments_total' => number_format((float) ($model->adjustments_total ?? 0), 2, '.', ''),
            'net_amount' => number_format((float) ($model->net_amount ?? 0), 2, '.', ''),
            'total_amount' => number_format((float) ($model->total_amount ?? 0), 2, '.', ''),
        ];
        $previousAmount = $this->cleaning->currentCleaningAmount($model);
        $adjustment = $this->cleaning->apply($model, $amount, $userId);

        $model->refresh();

        $this->audit->record(
            $amount > 0
                ? ($previousAmount > 0 ? 'SETTLEMENT_CLEANING_UPDATED' : 'SETTLEMENT_CLEANING_SET')
                : 'SETTLEMENT_CLEANING_REMOVED',
            'staff_settlement',
            $settlementId,
            [
                'previous_amount' => number_format($previousAmount, 2, '.', ''),
                'new_amount' => number_format($amount, 2, '.', ''),
                'before' => $before,
                'after' => [
                    'adjustments_total' => number_format((float) ($model->adjustments_total ?? 0), 2, '.', ''),
                    'net_amount' => number_format((float) ($model->net_amount ?? 0), 2, '.', ''),
                    'total_amount' => number_format((float) ($model->total_amount ?? 0), 2, '.', ''),
                ],
                'adjustment_id' => $adjustment?->id,
            ],
        );

        $detail = $this->settlements->findById($settlementId, $tenant->id, $branch->id, null);

        return OperationResult::ok('Cobro de limpieza guardado.', [
            'settlement' => SettlementMapper::settlement($detail['settlement'] ?? []),
            'adjustment' => $adjustment !== null
                ? SettlementMapper::adjustment([
                    'id' => $adjustment->id,
                    'adjustment_type' => $adjustment->adjustment_type,
                    'amount' => number_format((float) $adjustment->amount, 2, '.', ''),
                    'discount_mode' => $adjustment->discount_mode,
                    'discount_value' => $adjustment->discount_value !== null
                        ? number_format((float) $adjustment->discount_value, 2, '.', '')
                        : null,
                    'calculation_base' => $adjustment->calculation_base !== null
                        ? number_format((float) $adjustment->calculation_base, 2, '.', '')
                        : null,
                    'notes' => $adjustment->notes,
                    'dedup_key' => $adjustment->dedup_key,
                    'created_at' => $adjustment->created_at?->format('Y-m-d H:i:s'),
                ])
                : null,
            'cleaning_amount' => number_format($amount, 2, '.', ''),
            'previous_cleaning_amount' => number_format($previousAmount, 2, '.', ''),
        ]);
    }
}