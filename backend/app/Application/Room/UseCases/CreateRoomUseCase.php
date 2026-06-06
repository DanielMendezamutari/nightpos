<?php

declare(strict_types=1);

namespace App\Application\Room\UseCases;

use App\Application\Room\DTOs\CreateRoomInput;
use App\Application\Room\Services\RoomTypeResolver;
use App\Domain\Room\Exceptions\RoomDomainException;
use App\Domain\Room\Repositories\RoomRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateRoomUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly RoomRepositoryInterface $rooms,
        private readonly RoomTypeResolver $roomTypeResolver,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateRoomInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw RoomDomainException::branchRequired();
        }

        $type = $this->roomTypeResolver->resolve($tenant->id, $input->roomTypeId, $input->roomType);

        $code = trim($input->code);
        if ($code === '' || $this->rooms->codeExists($tenant->id, $branch->id, $code)) {
            throw RoomDomainException::duplicateCode();
        }

        $duration = $input->defaultDurationMinutes;
        if ($duration !== null && ($duration < 1 || $duration > 24 * 60)) {
            throw RoomDomainException::invalidDuration();
        }

        $suggestedPrice = null;
        if ($input->suggestedPrice !== null) {
            $price = (float) $input->suggestedPrice;
            if ($price < 0) {
                throw RoomDomainException::invalidStatus();
            }
            $suggestedPrice = number_format($price, 2, '.', '');
        }

        $room = $this->rooms->create(
            tenantId: $tenant->id,
            branchId: $branch->id,
            code: $code,
            name: trim($input->name),
            roomType: $type,
            defaultDurationMinutes: $duration,
            suggestedPrice: $suggestedPrice,
            notes: $input->notes,
        );

        return OperationResult::ok('Habitación creada.', ['room' => $room]);
    }
}
