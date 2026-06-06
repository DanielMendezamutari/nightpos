<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Http\Request;

final class ListCashMovementReasonsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly CashMovementReasonRepositoryInterface $reasons,
        private readonly Request $request,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $type = $this->request->query('type');
        $activeOnly = $this->request->boolean('active_only');

        return OperationResult::ok('Motivos de caja.', [
            'cash_movement_reasons' => $this->reasons->listForBranch(
                $tenant->id,
                $branch->id,
                is_string($type) ? $type : null,
                $activeOnly,
            ),
        ]);
    }
}
