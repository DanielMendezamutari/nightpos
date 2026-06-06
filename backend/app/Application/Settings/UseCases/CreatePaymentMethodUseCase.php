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

final class CreatePaymentMethodUseCase implements UseCaseInterface
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

        $code = strtoupper(trim((string) ($input->code ?? '')));
        $name = trim((string) ($input->name ?? ''));
        $type = strtoupper(trim((string) ($input->type ?? '')));

        if ($code === '' || $name === '' || ! in_array($type, ['CASH', 'QR', 'CARD', 'OTHER'], true)) {
            return OperationResult::fail('Código, nombre y tipo son obligatorios.');
        }

        if ($this->paymentMethods->codeExists($tenant->id, $code)) {
            throw MasterDataDomainException::duplicate();
        }

        $item = $this->paymentMethods->create(
            $tenant->id,
            isset($input->branchScoped) && $input->branchScoped ? $branch->id : null,
            $code,
            $name,
            $type,
            (bool) ($input->enabled ?? true),
            (bool) ($input->requiresReference ?? false),
        );

        return OperationResult::ok('Método de pago creado.', ['payment_method' => $item]);
    }
}
