<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class ResetPinInput extends DataTransferObject
{
    public function __construct(
        public int $userId,
        public string $pin,
    ) {
    }
}
