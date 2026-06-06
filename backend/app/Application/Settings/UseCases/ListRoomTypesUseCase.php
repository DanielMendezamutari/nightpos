<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Repositories\RoomTypeCatalogRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Http\Request;

final class ListRoomTypesUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly RoomTypeCatalogRepositoryInterface $roomTypes,
        private readonly Request $request,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        return OperationResult::ok('Tipos de habitación.', [
            'room_types' => $this->roomTypes->listForBranch(
                $tenant->id,
                $branch->id,
                $this->request->boolean('active_only'),
            ),
        ]);
    }
}
