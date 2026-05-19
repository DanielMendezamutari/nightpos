<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\Exceptions;

use RuntimeException;

final class PosFlowException extends RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        string $message,
    ) {
        parent::__construct($message);
    }
}
