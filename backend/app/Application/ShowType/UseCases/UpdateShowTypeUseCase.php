<?php

declare(strict_types=1);

namespace App\Application\ShowType\UseCases;

use App\Domain\ShowType\Exceptions\ShowTypeDomainException;
use App\Domain\ShowType\Repositories\ShowTypeRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateShowTypeUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly ShowTypeRepositoryInterface $showTypes,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! is_object($input) || ! isset($input->id)) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $existing = $this->showTypes->findById((int) $input->id, $tenant->id);

        if ($existing === null) {
            throw ShowTypeDomainException::notFound();
        }

        $name = trim((string) ($input->name ?? ''));
        if ($name === '') {
            return OperationResult::fail('El nombre es obligatorio.');
        }

        if ($this->showTypes->nameExists($tenant->id, $name, (int) $input->id)) {
            throw ShowTypeDomainException::duplicateName();
        }

        $suggested = isset($input->suggestedPrice) && $input->suggestedPrice !== null && $input->suggestedPrice !== ''
            ? number_format((float) $input->suggestedPrice, 2, '.', '')
            : null;

        $showType = $this->showTypes->update(
            id: (int) $input->id,
            tenantId: $tenant->id,
            name: $name,
            suggestedPrice: $suggested,
            status: (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Tipo de show actualizado.', ['show_type' => $showType]);
    }
}
