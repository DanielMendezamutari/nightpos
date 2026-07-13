<?php

declare(strict_types=1);

namespace App\Application\Reports\UseCases;

use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetShiftClosureCheckUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly ReportReadRepositoryInterface $reports,
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $openShifts = $this->shifts->listOpenForBranch($tenant->id, $branch->id);
        $openShiftIds = array_map(static fn ($shift) => $shift->id, $openShifts);
        $selectedShift = $openShifts[0] ?? null;
        $hasOpenShiftConflict = count($openShifts) > 1;

        $openCashSessions = $this->shifts->listOpenCashSessionsForBranch($tenant->id, $branch->id);
        $historicalOpenCashSessions = array_values(array_filter(
            $openCashSessions,
            static fn (array $session): bool => ! in_array($session['official_shift_id'], $openShiftIds, true),
        ));

        $shiftContext = [
            'selected_shift_id' => $selectedShift?->id,
            'open_shift_ids' => $openShiftIds,
            'has_open_shift_conflict' => $hasOpenShiftConflict,
            'open_cash_sessions_on_historical_shifts' => $historicalOpenCashSessions,
        ];

        if ($selectedShift === null) {
            $blockers = [['code' => 'no_open_shift', 'message' => 'No hay turno abierto actualmente.', 'count' => 0]];

            if ($historicalOpenCashSessions !== []) {
                $blockers[] = [
                    'code' => 'open_cash_sessions_on_historical_shifts',
                    'message' => 'Hay cajas abiertas vinculadas a turnos que ya no estan abiertos.',
                    'count' => count($historicalOpenCashSessions),
                ];
            }

            return OperationResult::ok('No hay turno abierto.', [
                'shift'     => null,
                'can_close' => false,
                'blockers'  => $blockers,
                'warnings'  => [],
                'summary'   => [],
                'shift_context' => $shiftContext,
            ]);
        }

        $check = $this->reports->getShiftClosureCheck($tenant->id, $branch->id, $selectedShift->id);

        if ($hasOpenShiftConflict) {
            $check['blockers'][] = [
                'code' => 'open_shift_conflict',
                'message' => 'Hay mas de un turno abierto en la sucursal.',
                'count' => count($openShifts),
            ];
        }

        if ($historicalOpenCashSessions !== []) {
            $check['blockers'][] = [
                'code' => 'open_cash_sessions_on_historical_shifts',
                'message' => 'Hay cajas abiertas vinculadas a turnos que ya no estan abiertos.',
                'count' => count($historicalOpenCashSessions),
            ];
        }

        $check['can_close'] = empty($check['blockers']);
        $check['shift_context'] = $shiftContext;

        return OperationResult::ok('Verificación de cierre de turno.', array_merge(
            ['shift_id' => $selectedShift->id, 'shift_name' => $selectedShift->name],
            $check,
        ));
    }
}
