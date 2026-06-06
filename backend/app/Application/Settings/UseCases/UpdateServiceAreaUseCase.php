<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateServiceAreaUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ServiceAreaRepositoryInterface $areas,
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
        $existing = $this->areas->findById($id, $tenant->id, $branch->id);

        if ($existing === null) {
            throw MasterDataDomainException::notFound();
        }

        $name = trim((string) ($input->name ?? ''));
        $areaType = strtoupper(trim((string) ($input->areaType ?? $existing['area_type'])));

        if ($name === '' || ! in_array($areaType, ['TABLE', 'VIP', 'BAR', 'ROOM', 'OTHER'], true)) {
            return OperationResult::fail('Datos inválidos.');
        }

        $item = $this->areas->update($id, $tenant->id, $name, $areaType, (string) ($input->status ?? 'active'));

        return OperationResult::ok('Ambiente actualizado.', ['service_area' => $item]);
    }
}
