<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\Services\OperationalShiftScheduleResolver;
use App\Domain\Shift\Entities\OfficialShift;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use Illuminate\Support\Carbon;
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
        $window = $this->scheduleResolver->resolveFor();

        if ($existing !== null && ! $this->shouldRotate($existing, $window)) {
            return $existing;
        }

        return DB::transaction(function () use ($tenantId, $branchId, $openedByUserId, $window) {
            $locked = $this->shifts->findOpenForBranch($tenantId, $branchId);

            if ($locked !== null) {
                if (! $this->shouldRotate($locked, $window)) {
                    return $locked;
                }

                // Turno AUTO vencido (otra ventana / now > ends_at): rotar.
                // Las liquidaciones/ventas viejas quedan en el turno cerrado (historial).
                $this->shifts->markAutoClosed($locked->id, $tenantId, $openedByUserId);
            }

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

    /**
     * Rota un turno AUTO vencido si hay uno abierto. No crea turno nuevo cuando
     * no existe ninguno (p. ej. tras cierre manual fiscal).
     */
    public function rotateStaleOpenShiftIfNeeded(int $tenantId, int $branchId, int $openedByUserId): ?OfficialShift
    {
        $existing = $this->shifts->findOpenForBranch($tenantId, $branchId);

        if ($existing === null) {
            return null;
        }

        $window = $this->scheduleResolver->resolveFor();

        if (! $this->shouldRotate($existing, $window)) {
            return $existing;
        }

        return DB::transaction(function () use ($tenantId, $branchId, $openedByUserId, $window) {
            $locked = $this->shifts->findOpenForBranch($tenantId, $branchId);

            if ($locked === null) {
                return null;
            }

            if (! $this->shouldRotate($locked, $window)) {
                return $locked;
            }

            $this->shifts->markAutoClosed($locked->id, $tenantId, $openedByUserId);

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

    /**
     * Solo se rota un turno ABIERTO automáticamente (creado por el sistema) cuando ya
     * venció: su ventana horaria terminó (now > ends_at) o ya no corresponde a la
     * ventana operativa actual (otro tipo de turno u otra fecha de negocio).
     *
     * Los turnos abiertos manualmente por un admin nunca se cierran solos: su cierre
     * es responsabilidad del admin (fiscalización).
     *
     * @param  array{shift_type: string, business_date: string}  $window
     */
    private function shouldRotate(OfficialShift $shift, array $window): bool
    {
        if (! $this->isAutoShift($shift)) {
            return false;
        }

        $sameWindow = $shift->shiftType === $window['shift_type']
            && substr($shift->businessDate, 0, 10) === substr($window['business_date'], 0, 10);

        if (! $sameWindow) {
            return true;
        }

        // Aún dentro de la misma ventana: rotar solo si su horario ya terminó.
        return Carbon::now()->greaterThan(Carbon::parse($shift->endsAt));
    }

    private function isAutoShift(OfficialShift $shift): bool
    {
        return $shift->notes !== null
            && str_contains($shift->notes, self::AUTO_SHIFT_NOTES);
    }
}
