<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Domain\Cash\Entities\CashSession;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;

/**
 * Resolución única de sesión de caja abierta para el usuario operador en tenant/sucursal.
 */
final class OpenCashSessionResolver
{
    public function __construct(
        private readonly CashSessionRepositoryInterface $sessions,
    ) {
    }

    public function findOpenForCurrentUser(int $tenantId, int $branchId, int $userId): ?CashSession
    {
        return $this->sessions->findOpenForUser($tenantId, $branchId, $userId);
    }
}
