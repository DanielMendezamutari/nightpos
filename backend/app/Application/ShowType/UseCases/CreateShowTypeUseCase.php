<?php

declare(strict_types=1);

namespace App\Application\ShowType\UseCases;

use App\Domain\ShowType\Exceptions\ShowTypeDomainException;
use App\Domain\ShowType\Repositories\ShowTypeRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateShowTypeUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ShowTypeRepositoryInterface $showTypes,
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

        $name = trim((string) ($input->name ?? ''));
        if ($name === '') {
            return OperationResult::fail('El nombre es obligatorio.');
        }

        if ($this->showTypes->nameExists($tenant->id, $name)) {
            throw ShowTypeDomainException::duplicateName();
        }

        $suggested = isset($input->suggestedPrice) && $input->suggestedPrice !== null && $input->suggestedPrice !== ''
            ? number_format((float) $input->suggestedPrice, 2, '.', '')
            : null;

        $showType = $this->showTypes->create(
            tenantId: $tenant->id,
            branchId: $branch->id,
            name: $name,
            suggestedPrice: $suggested,
            status: (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Tipo de show creado.', ['show_type' => $showType]);
    }
}
