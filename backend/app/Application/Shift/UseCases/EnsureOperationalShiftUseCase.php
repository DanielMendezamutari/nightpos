<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\Services\OperationalShiftScheduleResolver;
use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Garantiza un turno oficial abierto para clasificar operaciones (reportes).
 * No requiere permiso shifts.open — creación automática del sistema.
 */
final class EnsureOperationalShiftUseCase
{
    public const AUTO_SHIFT_NOTES = 'Turno creado automáticamente para clasificación de reportes';

    public function __construct(
        private readonly OfficialShiftRepositoryInterface $shifts,
        private readonly OperationalShiftScheduleResolver $scheduleResolver,
    ) {
    }

    public function execute(int $tenantId, int $branchId, int $openedByUserId): OfficialShift
    {
        $existing = $this->shifts->findOpenForBranch($tenantId, $branchId);

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($tenantId, $branchId, $openedByUserId) {
            $locked = $this->shifts->findOpenForBranch($tenantId, $branchId);

            if ($locked !== null) {
                return $locked;
            }

            $window = $this->scheduleResolver->resolveFor();

            return $this->shifts->open(
                tenantId: $tenantId,
                branchId: $branchId,
                name: $window['name'],
                shiftType: $window['shift_type'],
                businessDate: $window['business_date'],
                startsAt: $window['starts_at'],
                endsAt: $window['ends_at'],
                openedByUserId: $openedByUserId,
                notes: self::AUTO_SHIFT_NOTES,
            );
        });
    }
}
