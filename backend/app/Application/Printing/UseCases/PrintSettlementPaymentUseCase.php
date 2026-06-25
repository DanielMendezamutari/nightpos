<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\StaffSettlement\Services\SettlementPrintPresenter;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintSettlementPaymentUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CreateSettlementPaymentPrintJobUseCase $createPrintJob,
        private readonly SettlementPrintPresenter $presenter,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw new StaffSettlementNotFoundException();
        }

        $settlementId = (int) ($input->settlementId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);

        $model = StaffSettlementModel::query()
            ->where('id', $settlementId)
            ->where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->first();

        if ($model === null) {
            throw new StaffSettlementNotFoundException();
        }

        if ($model->status !== 'PAID') {
            throw StaffSettlementDomainException::settlementNotPaid();
        }

        $reprintNumber = null;
        $reprintedByName = null;

        if ($reprint) {
            $model->increment('print_count');
            $model->refresh();

            $reprintNumber = (int) $model->print_count;
            $reprintedByName = UserModel::query()->whereKey($userId)->value('name');

            $model->update([
                'last_printed_at' => now(),
                'last_printed_by_user_id' => $userId,
            ]);

            $this->audit->record(
                'SETTLEMENT_REPRINTED',
                'staff_settlement',
                $settlementId,
                [
                    'print_count' => $reprintNumber,
                    'ticket_number' => $model->ticket_number,
                ],
            );
        }

        $printResult = $this->createPrintJob->execute(
            settlementId: $settlementId,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            isReprint: $reprint,
            reprintNumber: $reprintNumber,
            reprintedByName: $reprintedByName,
        );

        if ($printResult['job'] !== null) {
            StaffSettlementModel::query()->whereKey($settlementId)->update([
                'print_job_id' => (int) $printResult['job']['id'],
                'last_printed_at' => now(),
                'last_printed_by_user_id' => $userId,
            ]);
        }

        $presented = $this->presenter->payment($settlementId, $tenant->id);

        return OperationResult::ok(
            $reprint ? 'Comprobante reenviado a impresión.' : 'Comprobante encolado.',
            [
                'settlement' => $presented['settlement'] ?? null,
                'print_job' => $printResult['job'],
                'print_warning' => $printResult['warning'],
            ],
        );
    }
}
