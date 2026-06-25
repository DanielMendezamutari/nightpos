<?php

declare(strict_types=1);

namespace App\Application\Cash\UseCases;

use App\Application\Cash\Services\CashSessionCloseCheckBuilder;
use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionStatus;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetCashSessionCloseCheckAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly CashSessionCloseCheckBuilder $closeCheckBuilder,
        private readonly CashSessionFinancialSummaryBuilder $financials,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('admin.cash_sessions.force_close')) {
            throw PermissionDeniedException::forPermission('admin.cash_sessions.force_close');
        }

        $tenant = $this->tenantContext->tenant();
        $sessionId = (int) ($input->sessionId ?? 0);

        if ($tenant === null || $sessionId <= 0) {
            throw new CashSessionNotFoundException();
        }

        $model = $this->assertAccessibleSession($sessionId, $tenant->id);

        if ($model->status !== CashSessionStatus::OPEN) {
            throw CashDomainException::sessionClosed();
        }

        $shiftId = $model->official_shift_id;

        if ($shiftId === null) {
            return OperationResult::ok('La caja no tiene turno asociado.', [
                'can_close' => false,
                'blockers' => [[
                    'type' => 'NO_SHIFT_ON_SESSION',
                    'code' => 'no_shift_on_session',
                    'count' => 0,
                    'message' => 'La sesión de caja no tiene turno oficial asociado.',
                ]],
                'warnings' => [],
                'actions' => [],
                'summary' => [],
                'cash_session_id' => (int) $model->id,
            ]);
        }

        $check = $this->closeCheckBuilder->build(
            $tenant->id,
            (int) $model->branch_id,
            (int) $shiftId,
            (int) $model->id,
        );

        $summary = $this->financials->build(
            sessionId: (int) $model->id,
            openingAmount: (string) $model->opening_amount,
            storedExpectedAmount: $model->expected_amount !== null ? (string) $model->expected_amount : null,
            declaredClosingAmount: $model->declared_closing_amount !== null ? (string) $model->declared_closing_amount : null,
            differenceAmount: $model->difference_amount !== null ? (string) $model->difference_amount : null,
            status: $model->status,
        );

        return OperationResult::ok('Verificación de cierre administrativo.', array_merge($check, [
            'cash_session_id' => (int) $model->id,
            'official_shift_id' => (int) $shiftId,
            'session' => [
                'id' => (int) $model->id,
                'cashier' => $model->opener ? [
                    'id' => (int) $model->opener->id,
                    'name' => $model->opener->name,
                ] : null,
                'opened_at' => $model->opened_at?->toIso8601String(),
                'official_shift' => $model->officialShift ? [
                    'id' => (int) $model->officialShift->id,
                    'shift_type' => $model->officialShift->shift_type,
                    'business_date' => $model->officialShift->business_date,
                ] : null,
            ],
            'financial_preview' => [
                'expected_cash' => $summary['expected_cash'],
                'total_sales' => $summary['total_sales'],
                'total_manual_expense' => $summary['total_manual_expense'],
            ],
        ]));
    }

    private function assertAccessibleSession(int $sessionId, int $tenantId): CashSessionModel
    {
        $model = $this->cashSessions->findModelForAdmin($sessionId, $tenantId);

        if ($model === null) {
            throw new CashSessionNotFoundException();
        }

        $branch = $this->branchContext->branch();

        if ($branch === null) {
            throw CashDomainException::branchRequired();
        }

        if (! $this->staffContext->isSuperAdmin() && (int) $model->branch_id !== $branch->id) {
            throw BranchAccessDeniedException::create();
        }

        return $model;
    }
}
