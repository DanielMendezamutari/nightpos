<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\ServiceTableRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateServiceTableUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ServiceTableRepositoryInterface $tables,
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

        $code = trim((string) ($input->code ?? ''));
        $label = trim((string) ($input->label ?? ''));
        $serviceAreaId = (int) ($input->serviceAreaId ?? 0);
        $sortOrder = (int) ($input->sortOrder ?? 0);

        if ($code === '' || $label === '' || $serviceAreaId <= 0) {
            return OperationResult::fail('Salón, código y etiqueta son obligatorios.');
        }

        if ($this->tables->codeExists($branch->id, $code)) {
            throw MasterDataDomainException::duplicate();
        }

        $item = $this->tables->create(
            $tenant->id,
            $branch->id,
            $serviceAreaId,
            $code,
            $label,
            $sortOrder,
            (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Mesa creada.', ['service_table' => $item]);
    }
}
