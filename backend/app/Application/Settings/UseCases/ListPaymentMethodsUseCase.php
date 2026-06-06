<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Http\Request;

final class ListPaymentMethodsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PaymentMethodRepositoryInterface $paymentMethods,
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

        $enabledOnly = $this->request->boolean('active_only') || $this->request->boolean('enabled_only');

        return OperationResult::ok('Métodos de pago.', [
            'payment_methods' => $this->paymentMethods->listForBranch($tenant->id, $branch->id, $enabledOnly),
        ]);
    }
}
