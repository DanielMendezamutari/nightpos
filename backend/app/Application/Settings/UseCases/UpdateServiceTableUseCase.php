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

final class UpdateServiceTableUseCase implements UseCaseInterface
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
        $id = (int) ($input->id ?? 0);

        if ($tenant === null || $branch === null || $id <= 0) {
            throw UserDomainException::branchNotInTenant();
        }

        $existing = $this->tables->findById($id, $tenant->id, $branch->id);
        if ($existing === null) {
            throw MasterDataDomainException::notFound();
        }

        $label = trim((string) ($input->label ?? ''));
        if ($label === '') {
            return OperationResult::fail('La etiqueta es obligatoria.');
        }

        $item = $this->tables->update(
            $id,
            $tenant->id,
            $branch->id,
            $label,
            (int) ($input->sortOrder ?? $existing['sort_order']),
            (string) ($input->status ?? $existing['status']),
        );

        return OperationResult::ok('Mesa actualizada.', ['service_table' => $item]);
    }
}
