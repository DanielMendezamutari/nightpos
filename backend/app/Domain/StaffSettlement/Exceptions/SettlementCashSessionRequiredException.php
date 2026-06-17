<?php

declare(strict_types=1);

namespace App\Domain\StaffSettlement\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

/**
 * Caja abierta requerida para pagar liquidación (con contexto de diagnóstico).
 */
final class SettlementCashSessionRequiredException extends DomainException
{
    /**
     * @param array<string, mixed> $debugContext
     */
    public function __construct(
        public readonly array $debugContext,
    ) {
        parent::__construct('Debe abrir caja para pagar esta liquidación.');
    }
}
