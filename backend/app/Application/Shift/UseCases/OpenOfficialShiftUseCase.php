<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\DTOs\OpenOfficialShiftInput;
use App\Application\Shift\Services\OfficialShiftWindowBuilder;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Exceptions\ShiftDomainException;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Domain\Shift\ValueObjects\ShiftType;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class OpenOfficialShiftUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly OfficialShiftWindowBuilder $windowBuilder,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof OpenOfficialShiftInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        try {
            $type = ShiftType::fromString($input->shiftType);
        } catch (\InvalidArgumentException) {
            throw ShiftDomainException::invalidShiftType($input->shiftType);
        }

        if ($this->shifts->findOpenForBranch($tenant->id, $branch->id) !== null) {
            throw ShiftDomainException::shiftAlreadyOpen();
        }

        $window = $this->windowBuilder->build($type->value, $input->businessDate);

        $shift = $this->shifts->open(
            tenantId: $tenant->id,
            branchId: $branch->id,
            name: $window['name'],
            shiftType: $type->value,
            businessDate: $input->businessDate,
            startsAt: $window['starts_at'],
            endsAt: $window['ends_at'],
            openedByUserId: $userId,
            notes: $input->notes,
        );

        return OperationResult::ok('Turno oficial abierto correctamente.', [
            'shift' => ShiftMapper::shift($shift),
        ]);
    }
}
