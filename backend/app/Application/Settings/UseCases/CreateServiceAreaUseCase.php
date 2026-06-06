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

final class CreateServiceAreaUseCase implements UseCaseInterface
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

        $code = trim((string) ($input->code ?? ''));
        $name = trim((string) ($input->name ?? ''));
        $areaType = strtoupper(trim((string) ($input->areaType ?? 'TABLE')));

        if ($code === '' || $name === '') {
            return OperationResult::fail('Código y nombre son obligatorios.');
        }

        if (! in_array($areaType, ['TABLE', 'VIP', 'BAR', 'ROOM', 'OTHER'], true)) {
            return OperationResult::fail('Tipo de ambiente inválido.');
        }

        if ($this->areas->codeExists($branch->id, $code)) {
            throw MasterDataDomainException::duplicate();
        }

        $item = $this->areas->create(
            $tenant->id,
            $branch->id,
            $code,
            $name,
            $areaType,
            (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Ambiente creado.', ['service_area' => $item]);
    }
}
