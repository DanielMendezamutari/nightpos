<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Application\Cash\Support\AdminCashSessionMapper;
use App\Application\Cash\Support\CashMapper;
use App\Application\Sale\Support\SaleMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashSessionAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly SaleRepositoryInterface $sales,
        private readonly CashSessionFinancialSummaryBuilder $financials,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('admin.cash_sessions.view')) {
            throw PermissionDeniedException::forPermission('admin.cash_sessions.view');
        }

        $tenant = $this->tenantContext->tenant();
        $sessionId = (int) ($input->sessionId ?? 0);

        if ($tenant === null || $sessionId <= 0) {
            throw new CashSessionNotFoundException();
        }

        $model = $this->cashSessions->findModelForAdmin($sessionId, $tenant->id);

        if ($model === null) {
            throw new CashSessionNotFoundException();
        }

        $entity = $this->cashSessions->findById($sessionId, $tenant->id, true);

        if ($entity === null) {
            throw new CashSessionNotFoundException();
        }

        $summary = $this->financials->build(
            sessionId: $sessionId,
            openingAmount: (string) $model->opening_amount,
            storedExpectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
            declaredClosingAmount: $model->declared_closing_amount !== null ? (string) $model->declared_closing_amount : null,
            differenceAmount: $model->difference_amount !== null ? (string) $model->difference_amount : null,
            status: $model->status,
        );

        $saleEntities = $this->sales->listForBranch($tenant->id, (int) $model->branch_id, $sessionId);
        $sales = array_map(static fn ($sale) => SaleMapper::saleSummary($sale), $saleEntities);

        $settlements = StaffSettlementModel::query()
            ->with(['staffUser', 'paidBy'])
            ->where('cash_session_id', $sessionId)
            ->where('status', 'PAID')
            ->orderByDesc('paid_at')
            ->get()
            ->map(static fn (StaffSettlementModel $row) => [
                'id' => (int) $row->id,
                'settlement_type' => $row->settlement_type,
                'staff_name' => $row->staffUser?->name,
                'total_amount' => (string) $row->total_amount,
                'paid_at' => $row->paid_at?->toIso8601String(),
                'paid_by_name' => $row->paidBy?->name,
            ])
            ->all();

        $movements = CashMapper::session($entity)['movements'] ?? [];

        $incomeMovements = array_values(array_filter(
            $movements,
            static fn (array $m) => $m['movement_type'] === 'INCOME',
        ));

        $expenseMovements = array_values(array_filter(
            $movements,
            static fn (array $m) => $m['movement_type'] === 'EXPENSE',
        ));

        return OperationResult::ok('Detalle de sesión de caja.', [
            'session' => array_merge(
                AdminCashSessionMapper::listItem($model, $summary),
                [
                    'opening_notes' => $model->opening_notes,
                    'closing_notes' => $model->closing_notes,
                    'closed_by' => $model->closer ? [
                        'id' => (int) $model->closer->id,
                        'name' => $model->closer->name,
                    ] : null,
                ],
            ),
            'summary' => array_merge($summary, [
                'sales_by_method' => $this->financials->salesByMethod($sessionId),
            ]),
            'movements' => $movements,
            'income_movements' => $incomeMovements,
            'expense_movements' => $expenseMovements,
            'sales' => $sales,
            'settlements_paid' => $settlements,
        ]);
    }
}
