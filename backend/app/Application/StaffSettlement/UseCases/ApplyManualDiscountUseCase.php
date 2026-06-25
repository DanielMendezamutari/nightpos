<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementManualDiscountService;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ApplyManualDiscountUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly SettlementManualDiscountService $discounts,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.fines.manage')) {
            throw PermissionDeniedException::forPermission('settlements.fines.manage');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();
        $settlementId = (int) ($input->settlementId ?? 0);
        $discountMode = strtoupper(trim((string) ($input->discountMode ?? '')));
        $discountValue = (float) ($input->discountValue ?? 0);
        $reason = trim((string) ($input->reason ?? ''));
        $notes = $input->notes ?? null;

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

        $hadDiscount = $model->adjustments()
            ->where('adjustment_type', 'MANUAL_DISCOUNT')
            ->exists();

        $adjustment = $this->discounts->apply(
            $model,
            $discountMode,
            $discountValue,
            $reason,
            $notes,
            $userId,
        );

        $model->refresh();

        $this->audit->record(
            $hadDiscount ? 'MANUAL_DISCOUNT_UPDATED' : 'MANUAL_DISCOUNT_CREATED',
            'staff_settlement',
            $settlementId,
            [
                'discount_mode' => $discountMode,
                'discount_value' => number_format($discountValue, 2, '.', ''),
                'amount' => number_format((float) $adjustment->amount, 2, '.', ''),
            ],
        );

        $detail = $this->settlements->findById($settlementId, $tenant->id, $branch->id, null);

        return OperationResult::ok(
            $hadDiscount ? 'Descuento manual actualizado.' : 'Descuento manual registrado.',
            [
                'settlement' => SettlementMapper::settlement($detail['settlement'] ?? []),
                'adjustment' => SettlementMapper::adjustment([
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
                    'reason' => $reason,
                ]),
                'preview' => $this->discounts->preview($model, $discountMode, $discountValue),
            ],
        );
    }
}
