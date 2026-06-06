<?php

declare(strict_types=1);

namespace App\Shared\Application\Exceptions;

use Exception;

/**
 * Application-layer errors (orchestration, authorization at app level).
 */
class ApplicationException extends Exception
{
}
