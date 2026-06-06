<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\ListCashSessionsAdminInput;
use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Cash\Support\AdminCashSessionMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListCashSessionsAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly CashSessionFinancialSummaryBuilder $financials,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('admin.cash_sessions.list')) {
            throw PermissionDeniedException::forPermission('admin.cash_sessions.list');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Debe indicar la empresa en el contexto.');
        }

        $tenantId = $tenant->id;

        if ($input instanceof ListCashSessionsAdminInput
            && $input->tenantId !== null
            && $input->tenantId > 0
            && $this->staffContext->isSuperAdmin()) {
            $tenantId = $input->tenantId;
        }

        $branchId = $input instanceof ListCashSessionsAdminInput && $input->branchId !== null && $input->branchId > 0
            ? $input->branchId
            : ($this->branchContext->branch()?->id);

        $filters = $input instanceof ListCashSessionsAdminInput ? $input : new ListCashSessionsAdminInput();

        $models = $this->cashSessions->listForAdmin(
            tenantId: $tenantId,
            branchId: $branchId,
            status: $filters->status,
            officialShiftId: $filters->officialShiftId,
            cashierUserId: $filters->cashierUserId,
            dateFrom: $filters->dateFrom,
            dateTo: $filters->dateTo,
        );

        $rows = [];

        foreach ($models as $model) {
            $financials = $this->financials->build(
                sessionId: (int) $model->id,
                openingAmount: (string) $model->opening_amount,
                storedExpectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
                declaredClosingAmount: $model->declared_closing_amount !== null ? (string) $model->declared_closing_amount : null,
                differenceAmount: $model->difference_amount !== null ? (string) $model->difference_amount : null,
                status: $model->status,
            );

            $rows[] = AdminCashSessionMapper::listItem($model, $financials);
        }

        return OperationResult::ok('Sesiones de caja listadas.', ['cash_sessions' => $rows]);
    }
}
