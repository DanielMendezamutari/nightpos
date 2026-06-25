<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\Cash\Services\OpenCashSessionResolver;

use App\Application\StaffSettlement\Services\SettlementFineApplier;

use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;

use App\Domain\Auth\Exceptions\PermissionDeniedException;

use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;

use App\Domain\StaffSettlement\Exceptions\StaffSettlementNotFoundException;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class GetSettlementPayPreviewUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly SettlementFineApplier $fineApplier,

        private readonly SettlementShiftScopeResolver $scopeResolver,

        private readonly OpenCashSessionResolver $cashSessionResolver,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $this->staffContext->hasPermission('settlements.pay')) {

            throw PermissionDeniedException::forPermission('settlements.pay');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $cashierId = $this->staffContext->userId();

        $settlementId = (int) ($input->settlementId ?? 0);

        $appliedFineIds = is_array($input->appliedFineIds ?? null) ? $input->appliedFineIds : [];



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



        if ($model->status !== 'PENDING') {

            throw StaffSettlementDomainException::alreadyPaid();

        }



        $this->assertCashSessionScope($model, $tenant->id, $branch->id, $cashierId);



        $preview = $this->fineApplier->buildPayPreview($model, $appliedFineIds);



        return OperationResult::ok('Vista previa de pago.', $preview);

    }



    private function assertCashSessionScope(

        StaffSettlementModel $model,

        int $tenantId,

        int $branchId,

        int $cashierId,

    ): void {

        if (! $this->scopeResolver->usesMyCashSessionScope()) {

            return;

        }



        $openSession = $this->cashSessionResolver->resolveOpenCashSessionForUser($tenantId, $branchId, $cashierId);



        if ($openSession === null) {

            throw StaffSettlementDomainException::cashRequiredForPayment();

        }



        if ($model->cash_session_id === null || (int) $model->cash_session_id !== $openSession->id) {

            throw StaffSettlementDomainException::cannotPayOtherCashSession();

        }

    }

}


