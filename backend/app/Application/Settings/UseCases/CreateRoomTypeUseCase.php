<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Exceptions\MasterDataDomainException;
use App\Domain\Settings\Repositories\RoomTypeCatalogRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateRoomTypeUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly RoomTypeCatalogRepositoryInterface $roomTypes,
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

        if ($code === '' || $name === '') {
            return OperationResult::fail('Código y nombre son obligatorios.');
        }

        if ($this->roomTypes->codeExists($tenant->id, $code)) {
            throw MasterDataDomainException::duplicate();
        }

        $duration = (int) ($input->defaultDurationMinutes ?? 60);
        $price = number_format((float) ($input->suggestedPrice ?? 0), 2, '.', '');

        $item = $this->roomTypes->create(
            $tenant->id,
            isset($input->branchScoped) && $input->branchScoped ? $branch->id : null,
            $code,
            $name,
            max(1, $duration),
            $price,
            (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Tipo de habitación creado.', ['room_type' => $item]);
    }
}
