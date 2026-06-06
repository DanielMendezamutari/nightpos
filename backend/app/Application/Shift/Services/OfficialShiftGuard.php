<?php

declare(strict_types=1);

namespace App\Application\Shift\Services;

use App\Application\Shift\UseCases\EnsureOperationalShiftUseCase;
use App\Domain\Shift\Entities\OfficialShift;

/**
 * @deprecated Prefer EnsureOperationalShiftUseCase — los turnos ya no bloquean operación.
 */
final class OfficialShiftGuard
{
    public function __construct(
        private readonly EnsureOperationalShiftUseCase $ensureOperationalShift,
    ) {
    }

    public function requireOpen(int $tenantId, int $branchId, int $openedByUserId): OfficialShift
    {
        return $this->ensureOperationalShift->execute($tenantId, $branchId, $openedByUserId);
    }
}
