<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\UseCases;

use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Application\StaffSettlement\Support\SettlementMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;
use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class MarkSettlementPaidUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly StaffSettlementRepositoryInterface $settlements,
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly CashMovementReasonRepositoryInterface $cashReasons,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->hasPermission('settlements.pay')) {
            throw PermissionDeniedException::forPermission('settlements.pay');
        }

        $tenant    = $this->tenantContext->tenant();
        $branch    = $this->branchContext->branch();
        $cashierId = $this->staffContext->userId();
        $settlementId = (int) ($input->settlementId ?? 0);
        $notes     = $input->notes ?? null;

        if ($tenant === null || $branch === null || $cashierId === null || $settlementId <= 0) {
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

        if ($model->status === 'PAID') {
            throw StaffSettlementDomainException::alreadyPaid();
        }

        if ($model->status === 'CANCELLED') {
            throw StaffSettlementDomainException::cannotPayCancelled();
        }

        $settlement = DB::transaction(function () use ($model, $tenant, $branch, $cashierId, $settlementId, $notes) {
            $session = $this->cashSessionResolver->findOpenForCurrentUser($tenant->id, $branch->id, $cashierId);

            if ($session === null) {
                throw StaffSettlementDomainException::cashRequiredForPayment();
            }

            $reason    = $this->resolveExpenseReason($model->settlement_type, $tenant->id, $branch->id);
            $staffName = $model->staffUser?->name ?? $this->staffNameFallback($model->settlement_type);
            $description = $reason['name'].' — '.$staffName;

            if ($notes !== null && trim($notes) !== '') {
                $description .= ' — '.trim($notes);
            }

            $this->cashSessions->addMovement(
                tenantId: $tenant->id,
                branchId: $branch->id,
                cashSessionId: $session->id,
                movementType: 'EXPENSE',
                amount: (string) $model->total_amount,
                description: $description,
                paymentMethod: 'CASH',
                createdByUserId: $cashierId,
                cashMovementReasonId: (int) $reason['id'],
                notes: $notes,
            );

            StaffSettlementModel::query()
                ->where('id', $settlementId)
                ->update(['cash_session_id' => $session->id]);

            return $this->settlements->markPaid($settlementId, $tenant->id, $branch->id, $cashierId, $notes);
        });

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'settlement.paid',
            [
                'entity'  => ['type' => 'settlement', 'id' => $settlementId],
                'summary' => 'Liquidación pagada',
                'refresh' => ['settlements', 'cash'],
            ]
        );

        $this->eventEmitter->emit(
            $tenant->id,
            $branch->id,
            'cash.movement.created',
            [
                'entity'  => ['type' => 'settlement', 'id' => $settlementId],
                'summary' => 'Egreso en caja por liquidación',
                'refresh' => ['cash'],
            ]
        );

        return OperationResult::ok('Liquidación marcada como pagada.', [
            'settlement' => SettlementMapper::settlement($settlement),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveExpenseReason(string $settlementType, int $tenantId, int $branchId): array
    {
        $preferredName = match ($settlementType) {
            'WAITER'   => 'Comisión garzón',
            'GIRL'     => 'Pago chicas',
            'CLEANING' => 'Limpieza',
            default    => 'Limpieza',
        };

        $reasons = $this->cashReasons->listForBranch($tenantId, $branchId, 'EXPENSE', true);

        foreach ($reasons as $reason) {
            if (strcasecmp((string) $reason['name'], $preferredName) === 0) {
                return $reason;
            }
        }

        // Fallback: first available EXPENSE reason
        $first = $reasons[0] ?? null;

        if ($first === null) {
            throw StaffSettlementDomainException::cashRequiredForPayment();
        }

        return $first;
    }

    private function staffNameFallback(string $settlementType): string
    {
        return match ($settlementType) {
            'WAITER'   => 'Garzón',
            'GIRL'     => 'Chica',
            'CLEANING' => 'Limpieza',
            default    => 'Personal',
        };
    }
}
