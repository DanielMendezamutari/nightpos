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

final class UpdateRoomTypeUseCase implements UseCaseInterface
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

        $id = (int) ($input->id ?? 0);
        $existing = $this->roomTypes->findById($id, $tenant->id);

        if ($existing === null) {
            throw MasterDataDomainException::notFound();
        }

        $name = trim((string) ($input->name ?? ''));
        if ($name === '') {
            return OperationResult::fail('El nombre es obligatorio.');
        }

        $duration = (int) ($input->defaultDurationMinutes ?? $existing['default_duration_minutes']);
        $price = number_format((float) ($input->suggestedPrice ?? $existing['suggested_price']), 2, '.', '');

        $item = $this->roomTypes->update(
            $id,
            $tenant->id,
            $name,
            max(1, $duration),
            $price,
            (string) ($input->status ?? 'active'),
        );

        return OperationResult::ok('Tipo de habitación actualizado.', ['room_type' => $item]);
    }
}
