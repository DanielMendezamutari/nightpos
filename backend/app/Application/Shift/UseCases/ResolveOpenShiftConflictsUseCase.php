<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\DTOs\ResolveOpenShiftConflictsInput;
use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ResolveOpenShiftConflictsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof ResolveOpenShiftConflictsInput) {
            return OperationResult::fail('Entrada invalida.');
        }

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $openShifts = $this->shifts->listOpenForBranch($tenant->id, $branch->id);
        $openShiftIds = array_map(static fn ($shift) => $shift->id, $openShifts);

        if (count($openShifts) <= 1) {
            return OperationResult::ok('No hay conflicto de turnos abiertos.', [
                'resolved' => false,
                'kept_shift_id' => $openShifts[0]->id ?? null,
                'closed_count' => 0,
                'blocked_shift_ids' => [],
                'open_shifts' => array_map(static fn ($shift) => ShiftMapper::shiftListItem($shift), $openShifts),
            ]);
        }

        $protectedShiftIds = array_values(array_intersect(
            $this->shifts->openShiftIdsWithOpenCashSessions($tenant->id, $branch->id),
            $openShiftIds,
        ));

        if ($input->keepShiftId !== null && ! in_array($input->keepShiftId, $openShiftIds, true)) {
            return OperationResult::fail('El turno a conservar no esta abierto en la sucursal actual.');
        }

        if ($input->keepShiftId !== null) {
            $keeperId = $input->keepShiftId;
        } elseif (count($protectedShiftIds) === 1) {
            $keeperId = $protectedShiftIds[0];
        } elseif (count($protectedShiftIds) > 1) {
            return OperationResult::fail('Hay mas de un turno abierto con caja activa. Seleccione manualmente el turno a conservar.');
        } else {
            $keeperId = $openShifts[0]->id;
        }

        $closedIds = [];
        $blockedIds = [];

        foreach ($openShifts as $shift) {
            if ($shift->id === $keeperId) {
                continue;
            }

            if (in_array($shift->id, $protectedShiftIds, true)) {
                $blockedIds[] = $shift->id;

                continue;
            }

            $this->shifts->markAutoClosed($shift->id, $tenant->id, $userId);
            $closedIds[] = $shift->id;

            $this->audit->record(
                'official_shift.conflict_auto_closed',
                'official_shift',
                $shift->id,
                [
                    'kept_shift_id' => $keeperId,
                    'resolver_user_id' => $userId,
                ],
            );
        }

        $remainingOpen = $this->shifts->listOpenForBranch($tenant->id, $branch->id);

        return OperationResult::ok('Conflictos de turnos abiertos resueltos.', [
            'resolved' => count($closedIds) > 0,
            'kept_shift_id' => $keeperId,
            'closed_count' => count($closedIds),
            'closed_shift_ids' => $closedIds,
            'blocked_shift_ids' => $blockedIds,
            'open_shifts' => array_map(static fn ($shift) => ShiftMapper::shiftListItem($shift), $remainingOpen),
        ]);
    }
}
