<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\Cash\Services\OpenCashSessionResolver;
use App\Application\Printing\UseCases\CreateSettlementPaymentPrintJobUseCase;
use App\Application\StaffSettlement\Services\SettlementFineApplier;
use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;
use App\Application\StaffSettlement\Services\SettlementTicketNumberGenerator;

use App\Application\SSE\Services\OperationalEventEmitter;

use App\Application\StaffSettlement\Support\SettlementMapper;

use App\Domain\Auth\Exceptions\PermissionDeniedException;

use App\Domain\Cash\Entities\CashSession;

use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;

use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;

use App\Domain\StaffSettlement\Exceptions\SettlementCashSessionRequiredException;

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

use Illuminate\Database\QueryException;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;



final class MarkSettlementPaidUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly StaffSettlementRepositoryInterface $settlements,

        private readonly OpenCashSessionResolver $cashSessionResolver,

        private readonly SettlementShiftScopeResolver $scopeResolver,

        private readonly CashSessionRepositoryInterface $cashSessions,

        private readonly CashMovementReasonRepositoryInterface $cashReasons,

        private readonly OperationalEventEmitter $eventEmitter,

        private readonly SettlementFineApplier $fineApplier,

        private readonly SettlementTicketNumberGenerator $ticketNumbers,

        private readonly CreateSettlementPaymentPrintJobUseCase $createPrintJob,

        private readonly AuditLogRecorder $audit,

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

        $notes = $input->notes ?? null;
        $appliedFineIds = is_array($input->appliedFineIds ?? null) ? $input->appliedFineIds : [];

        $paymentMethod = strtoupper(trim((string) ($input->paymentMethod ?? '')));

        if (! in_array($paymentMethod, ['CASH', 'QR', 'CARD'], true)) {

            throw StaffSettlementDomainException::paymentMethodRequired();

        }



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



        if ($this->scopeResolver->usesMyCashSessionScope()) {

            $openSession = $this->cashSessionResolver->resolveOpenCashSessionForUser($tenant->id, $branch->id, $cashierId);

            if ($openSession === null) {

                throw StaffSettlementDomainException::cashRequiredForPayment();

            }

            if ($model->cash_session_id === null || (int) $model->cash_session_id !== $openSession->id) {

                throw StaffSettlementDomainException::cannotPayOtherCashSession();

            }

        }



        $sessionId = null;
        $cashMovementId = null;
        $ticketNumber = null;



        try {
            $settlement = DB::transaction(function () use ($model, $tenant, $branch, $cashierId, $settlementId, $notes, $paymentMethod, $appliedFineIds, &$sessionId, &$cashMovementId, &$ticketNumber) {

            $session = $this->requireOpenCashSession($tenant->id, $branch->id, $cashierId);

            $sessionId = $session->id;

            $this->fineApplier->applySelectedFines($model, $appliedFineIds, $cashierId);

            $model->refresh();

            $reason    = $this->resolveExpenseReason($model->settlement_type, $tenant->id, $branch->id);

            $staffName = $model->staffUser?->name ?? $this->staffNameFallback($model->settlement_type);

            $description = $reason['name'].' — '.$staffName;



            if ($notes !== null && trim($notes) !== '') {

                $description .= ' — '.trim($notes);

            }



            $movement = $this->cashSessions->addMovement(

                tenantId: $tenant->id,

                branchId: $branch->id,

                cashSessionId: $session->id,

                movementType: 'EXPENSE',

                amount: (string) $model->net_amount,

                description: $description,

                paymentMethod: $paymentMethod,

                createdByUserId: $cashierId,

                cashMovementReasonId: (int) $reason['id'],

                notes: $notes,

                sourceType: 'STAFF_SETTLEMENT',

                sourceId: $settlementId,

            );

            $cashMovementId = $movement->id;

            $ticketNumber = $this->ticketNumbers->next($tenant->id, $branch->id);



            StaffSettlementModel::query()

                ->where('id', $settlementId)

                ->update(['cash_session_id' => $session->id]);



            return $this->settlements->markPaid(

                $settlementId,

                $tenant->id,

                $branch->id,

                $cashierId,

                $notes,

                $paymentMethod,

                $cashMovementId,

                $ticketNumber,

            );

            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateTicketNumber($exception)) {
                Log::error('mark-paid duplicate ticket_number', [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'settlement_id' => $settlementId,
                    'ticket_number' => $ticketNumber,
                    'message' => $exception->getMessage(),
                ]);

                throw StaffSettlementDomainException::ticketNumberConflict();
            }

            throw $exception;
        }



        $printResult = $this->createPrintJob->execute(

            settlementId: $settlementId,

            tenantId: $tenant->id,

            branchId: $branch->id,

            requestedByUserId: $cashierId,

        );



        if ($printResult['job'] !== null) {

            StaffSettlementModel::query()->whereKey($settlementId)->update([

                'print_job_id' => (int) $printResult['job']['id'],

                'last_printed_at' => now(),

                'last_printed_by_user_id' => $cashierId,

            ]);
        }



        $this->audit->record(

            'SETTLEMENT_PAID',

            'staff_settlement',

            $settlementId,

            [

                'ticket_number' => $ticketNumber,

                'payment_method' => $paymentMethod,

                'cash_movement_id' => $cashMovementId,

                'net_amount' => $settlement['net_amount'] ?? null,

            ],

        );



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

            'cash_session_id' => $sessionId,

            'cash_movement_id' => $cashMovementId,

            'ticket_number' => $ticketNumber,

            'print_job' => $printResult['job'],

            'print_warning' => $printResult['warning'],

        ]);

    }



    private function requireOpenCashSession(int $tenantId, int $branchId, int $userId): CashSession

    {

        $session = $this->cashSessionResolver->resolveOpenCashSessionForUser($tenantId, $branchId, $userId);



        if ($session !== null) {

            return $session;

        }



        $debug = $this->buildCashSessionDebug($tenantId, $branchId, $userId);



        Log::warning('mark-paid: caja abierta no resuelta para el usuario.', $debug);



        throw new SettlementCashSessionRequiredException($debug);

    }



    /**

     * @return array<string, mixed>

     */

    private function buildCashSessionDebug(int $tenantId, int $branchId, int $userId): array

    {

        return [

            'auth_user_id' => $userId,

            'tenant_id' => $tenantId,

            'branch_id' => $branchId,

            'open_cash_session_found' => false,

            'open_cash_sessions_same_branch' => $this->cashSessions->listOpenSessionsForBranch($tenantId, $branchId),

        ];

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



        $first = $reasons[0] ?? null;



        if ($first === null) {

            throw StaffSettlementDomainException::expenseReasonRequired();

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

    private function isDuplicateTicketNumber(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, 'staff_settlements_ticket_scope_unique')
            || str_contains($message, 'staff_settlements_ticket_number_unique')
            || (str_contains($message, 'Duplicate entry') && str_contains($message, 'ticket_number'))
            || (str_contains($message, 'UNIQUE constraint failed') && str_contains($message, 'ticket_number'));
    }

}

