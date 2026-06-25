<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\StaffSettlement\Support\StaffFineMapper;

use App\Domain\Auth\Exceptions\PermissionDeniedException;

use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;

use App\Domain\StaffSettlement\Repositories\StaffFineRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class CancelStaffFineUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly StaffFineRepositoryInterface $fines,

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

        $fineId = (int) ($input->fineId ?? 0);

        $cancellationReason = trim((string) ($input->cancellationReason ?? ''));



        if ($tenant === null || $branch === null || $userId === null || $fineId <= 0) {

            throw StaffFineDomainException::reasonRequired();

        }



        if ($cancellationReason === '') {

            throw StaffFineDomainException::cancellationReasonRequired();

        }



        $fine = $this->fines->cancel($fineId, $tenant->id, $branch->id, $userId, $cancellationReason);

        $this->audit->record(
            'FINE_CANCELLED',
            'staff_fine',
            $fineId,
            ['cancellation_reason' => $cancellationReason],
        );

        return OperationResult::ok('Multa cancelada.', [

            'fine' => StaffFineMapper::fine($fine),

        ]);

    }

}


