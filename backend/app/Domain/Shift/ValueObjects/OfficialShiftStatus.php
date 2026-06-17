<?php

declare(strict_types=1);

namespace App\Domain\Shift\ValueObjects;

final class OfficialShiftStatus
{
    public const OPEN = 'OPEN';

    public const CLOSED = 'CLOSED';

    /**
     * Marca añadida a `notes` cuando un turno AUTO se cierra por rotación de horario.
     * Se mantiene status = CLOSED para no romper reportes/historial que filtran por CLOSED.
     */
    public const AUTO_CLOSE_NOTE = 'Cerrado automáticamente por rotación de turno';
}
