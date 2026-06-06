<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdatePaymentMethodUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PaymentMethodRepositoryInterface $paymentMethods,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! is_object($input)) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $id = (int) ($input->id ?? 0);
        $existing = $this->paymentMethods->findById($id, $tenant->id);

        if ($existing === null) {
            throw MasterDataDomainException::notFound();
        }

        $enabled = (bool) ($input->enabled ?? $existing['enabled']);
        $name = trim((string) ($input->name ?? $existing['name']));

        if ($name === '') {
            return OperationResult::fail('El nombre es obligatorio.');
        }

        if ($existing['type'] === 'CASH' && ! $enabled) {
            $enabledCash = array_filter(
                $this->paymentMethods->listForBranch($tenant->id, $branch->id, true),
                fn (array $m) => $m['type'] === 'CASH',
            );

            if (count($enabledCash) <= 1 && ($existing['enabled'] ?? false)) {
                throw MasterDataDomainException::cashRequired();
            }
        }

        $item = $this->paymentMethods->update(
            $id,
            $tenant->id,
            $name,
            $enabled,
            (bool) ($input->requiresReference ?? $existing['requires_reference']),
        );

        return OperationResult::ok('Método de pago actualizado.', ['payment_method' => $item]);
    }
}
