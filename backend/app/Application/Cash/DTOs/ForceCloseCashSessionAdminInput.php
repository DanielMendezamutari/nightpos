<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class ForceCloseCashSessionAdminInput
{
    public function __construct(
        public int $sessionId,
        public string $forcedCloseReason,
        public string $forcedCloseNotes,
    ) {
    }
}
