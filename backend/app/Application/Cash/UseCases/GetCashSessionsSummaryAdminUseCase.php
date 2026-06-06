<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\DTOs\ListCashSessionsAdminInput;
use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashSessionsSummaryAdminUseCase implements UseCaseInterface
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
        if (! $this->staffContext->hasPermission('admin.cash_sessions.summary')) {
            throw PermissionDeniedException::forPermission('admin.cash_sessions.summary');
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

        $openCount = 0;
        $closedCount = 0;
        $expectedCash = 0.0;
        $totalQr = 0.0;
        $totalCard = 0.0;
        $totalDifference = 0.0;
        $totalSales = 0.0;
        $totalExpense = 0.0;

        foreach ($models as $model) {
            if ($model->status === 'OPEN') {
                $openCount++;
            } else {
                $closedCount++;
            }

            $row = $this->financials->build(
                sessionId: (int) $model->id,
                openingAmount: (string) $model->opening_amount,
                storedExpectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
                declaredClosingAmount: $model->declared_closing_amount !== null ? (string) $model->declared_closing_amount : null,
                differenceAmount: $model->difference_amount !== null ? (string) $model->difference_amount : null,
                status: $model->status,
            );

            $expectedCash += (float) $row['expected_cash'];
            $totalQr += (float) $row['total_qr'];
            $totalCard += (float) $row['total_card'];
            $totalSales += (float) $row['total_sales'];
            $totalExpense += (float) $row['total_manual_expense'];

            if ($row['cash_difference'] !== null) {
                $totalDifference += (float) $row['cash_difference'];
            }
        }

        return OperationResult::ok('Resumen de fiscalización de cajas.', [
            'summary' => [
                'total_open_sessions' => $openCount,
                'total_closed_sessions' => $closedCount,
                'total_sessions' => count($models),
                'expected_cash_total' => number_format($expectedCash, 2, '.', ''),
                'total_qr' => number_format($totalQr, 2, '.', ''),
                'total_card' => number_format($totalCard, 2, '.', ''),
                'total_difference' => number_format($totalDifference, 2, '.', ''),
                'total_sales' => number_format($totalSales, 2, '.', ''),
                'total_expenses' => number_format($totalExpense, 2, '.', ''),
            ],
        ]);
    }
}
