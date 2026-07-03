<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\StaffSettlement\Services\SettlementTotalsCalculator;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class UpdateManualCompensationUseCase implements UseCaseInterface
{
    private const ADJUSTMENT_TYPE = 'MANUAL_COMPENSATION';

    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly SettlementTotalsCalculator $totalsCalculator,
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
        $actorId = $this->staffContext->userId();

        $settlementId = (int) ($input->settlementId ?? 0);
        $amount = (float) ($input->amount ?? -1);
        $notes = isset($input->notes) ? trim((string) $input->notes) : null;

        if ($tenant === null || $branch === null || $actorId === null || $settlementId <= 0) {
            throw new StaffSettlementNotFoundException();
        }

        if ($amount < 0) {
            throw StaffSettlementDomainException::invalidManualCompensationAmount();
        }

        /** @var StaffSettlementModel|null $settlement */
        $settlement = StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->first();

        if ($settlement === null) {
            throw new StaffSettlementNotFoundException();
        }

        if ($settlement->settlement_type !== 'WAITER') {
            throw StaffSettlementDomainException::manualCompensationOnlyForWaiters();
        }

        if ($settlement->status !== 'PENDING') {
            throw StaffSettlementDomainException::cannotModifyPaidSettlement();
        }

        if (($settlement->compensation_mode ?? null) !== 'MANUAL') {
            throw StaffSettlementDomainException::manualCompensationNotAllowedForMode();
        }

        $before = [
            'manual_amount_input' => $settlement->manual_amount_input,
            'gross_amount' => $settlement->gross_amount,
            'adjustments_total' => $settlement->adjustments_total,
            'net_amount' => $settlement->net_amount,
            'total_amount' => $settlement->total_amount,
        ];

        DB::transaction(function () use ($settlement, $amount, $notes, $actorId): void {
            $gross = (float) ($settlement->gross_amount ?? 0);

            $otherAdjustments = (float) StaffSettlementAdjustmentModel::query()
                ->where('staff_settlement_id', $settlement->id)
                ->where('adjustment_type', '!=', self::ADJUSTMENT_TYPE)
                ->sum('amount');

            $manualAdjustmentAmount = round($amount - ($gross + $otherAdjustments), 2);

            StaffSettlementAdjustmentModel::query()->updateOrCreate(
                [
                    'staff_settlement_id' => $settlement->id,
                    'adjustment_type' => self::ADJUSTMENT_TYPE,
                ],
                [
                    'tenant_id' => $settlement->tenant_id,
                    'branch_id' => $settlement->branch_id,
                    'staff_fine_id' => null,
                    'amount' => number_format($manualAdjustmentAmount, 2, '.', ''),
                    'discount_mode' => null,
                    'discount_value' => null,
                    'calculation_base' => number_format($gross, 2, '.', ''),
                    'notes' => $notes !== '' ? $notes : null,
                    'dedup_key' => sprintf('manual_compensation:%d', (int) $settlement->id),
                    'created_by_user_id' => $actorId,
                ],
            );

            StaffSettlementModel::query()
                ->where('id', $settlement->id)
                ->update([
                    'manual_amount_input' => number_format($amount, 2, '.', ''),
                    'compensation_source' => 'MANUAL_INPUT',
                    'compensation_locked_at' => now(),
                    'compensation_locked_by_user_id' => $actorId,
                    'compensation_notes' => $notes !== '' ? $notes : null,
                ]);

            $this->totalsCalculator->recalculate((int) $settlement->id);
        });

        $fresh = StaffSettlementModel::query()
            ->with(['staffUser', 'paidBy', 'items'])
            ->findOrFail($settlementId);

        $after = [
            'manual_amount_input' => $fresh->manual_amount_input,
            'gross_amount' => $fresh->gross_amount,
            'adjustments_total' => $fresh->adjustments_total,
            'net_amount' => $fresh->net_amount,
            'total_amount' => $fresh->total_amount,
        ];

        $this->audit->record(
            'SETTLEMENT_MANUAL_COMPENSATION_SET',
            'staff_settlement',
            $settlementId,
            [
                'before' => $before,
                'after' => $after,
                'compensation_mode' => $fresh->compensation_mode,
                'compensation_source' => $fresh->compensation_source,
                'notes' => $notes !== '' ? $notes : null,
            ],
        );

        return OperationResult::ok('Compensación manual guardada.', [
            'settlement' => SettlementMapper::settlement([
                'id' => $fresh->id,
                'tenant_id' => $fresh->tenant_id,
                'branch_id' => $fresh->branch_id,
                'official_shift_id' => $fresh->official_shift_id,
                'cash_session_id' => $fresh->cash_session_id,
                'staff_user_id' => $fresh->staff_user_id,
                'staff_name' => $fresh->staffUser?->name,
                'staff_role' => $fresh->staff_role,
                'settlement_type' => $fresh->settlement_type,
                'total_amount' => number_format((float) ($fresh->total_amount ?? 0), 2, '.', ''),
                'gross_amount' => number_format((float) ($fresh->gross_amount ?? 0), 2, '.', ''),
                'adjustments_total' => number_format((float) ($fresh->adjustments_total ?? 0), 2, '.', ''),
                'net_amount' => number_format((float) ($fresh->net_amount ?? 0), 2, '.', ''),
                'status' => $fresh->status,
                'paid_by_user_id' => $fresh->paid_by_user_id,
                'paid_by_name' => $fresh->paidBy?->name,
                'paid_at' => $fresh->paid_at?->format('Y-m-d H:i:s'),
                'payment_method' => $fresh->payment_method,
                'cash_movement_id' => $fresh->cash_movement_id,
                'ticket_number' => $fresh->ticket_number,
                'print_count' => (int) ($fresh->print_count ?? 0),
                'last_printed_at' => $fresh->last_printed_at?->format('Y-m-d H:i:s'),
                'last_printed_by_user_id' => $fresh->last_printed_by_user_id,
                'print_job_id' => $fresh->print_job_id,
                'version' => (int) ($fresh->version ?? 1),
                'has_ticket' => $fresh->ticket_number !== null,
                'notes' => $fresh->notes,
                'created_at' => $fresh->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $fresh->updated_at?->format('Y-m-d H:i:s'),
                'manual_amount_input' => $fresh->manual_amount_input !== null ? number_format((float) $fresh->manual_amount_input, 2, '.', '') : null,
                'compensation_mode' => $fresh->compensation_mode,
                'compensation_source' => $fresh->compensation_source,
                'compensation_notes' => $fresh->compensation_notes,
                'compensation_locked_at' => $fresh->compensation_locked_at?->format('Y-m-d H:i:s'),
                'compensation_locked_by_user_id' => $fresh->compensation_locked_by_user_id,
            ]),
        ]);
    }
}
