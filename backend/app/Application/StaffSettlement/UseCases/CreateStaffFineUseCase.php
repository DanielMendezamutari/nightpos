<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\Cash\Services\OpenCashSessionResolver;

use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;

use App\Application\StaffSettlement\Services\SettlementStaffValidator;

use App\Application\StaffSettlement\Support\StaffFineMapper;

use App\Domain\Auth\Exceptions\PermissionDeniedException;

use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;

use App\Domain\StaffSettlement\Repositories\StaffFineRepositoryInterface;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class CreateStaffFineUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly StaffFineRepositoryInterface $fines,

        private readonly StaffSettlementRepositoryInterface $settlements,

        private readonly SettlementStaffValidator $staffValidator,

        private readonly SettlementShiftScopeResolver $scopeResolver,

        private readonly OpenCashSessionResolver $cashSessionResolver,

        private readonly \App\Shared\Application\Support\AuditLogRecorder $audit,

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



        if ($tenant === null || $branch === null || $userId === null) {

            throw StaffFineDomainException::shiftRequired();

        }



        $staffUserId = (int) ($input->staffUserId ?? 0);

        $amount = (float) ($input->amount ?? 0);

        $reason = trim((string) ($input->reason ?? ''));

        $notes = $input->notes ?? null;

        $expectedRole = isset($input->staffRole) ? strtoupper(trim((string) $input->staffRole)) : null;



        if ($amount <= 0) {

            throw StaffFineDomainException::invalidAmount();

        }



        if ($reason === '') {

            throw StaffFineDomainException::reasonRequired();

        }



        $openShiftId = $this->settlements->resolveOpenShiftId($tenant->id, $branch->id);



        if ($openShiftId === null) {

            throw StaffFineDomainException::shiftRequired();

        }



        $staff = $this->staffValidator->assertStaffMember($tenant->id, $branch->id, $staffUserId, $expectedRole);



        $cashSessionId = null;



        if ($this->scopeResolver->usesMyCashSessionScope()) {

            $session = $this->cashSessionResolver->resolveOpenCashSessionForUser($tenant->id, $branch->id, $userId);



            if ($session === null) {

                throw StaffFineDomainException::cashSessionRequired();

            }



            $cashSessionId = $session->id;

        }



        $fine = $this->fines->create([

            'tenant_id' => $tenant->id,

            'branch_id' => $branch->id,

            'official_shift_id' => $openShiftId,

            'cash_session_id' => $cashSessionId,

            'staff_user_id' => $staffUserId,

            'staff_role' => $staff['staff_role'],

            'amount' => $amount,

            'reason' => $reason,

            'notes' => $notes,

            'created_by_user_id' => $userId,

        ]);

        $this->audit->record(
            'FINE_ADDED',
            'staff_fine',
            (int) $fine['id'],
            [
                'staff_user_id' => $staffUserId,
                'amount' => number_format($amount, 2, '.', ''),
                'reason' => $reason,
            ],
        );

        return OperationResult::ok('Multa registrada.', [

            'fine' => StaffFineMapper::fine($fine),

        ]);

    }

}


