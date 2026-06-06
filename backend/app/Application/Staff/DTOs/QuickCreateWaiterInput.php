<?php

declare(strict_types=1);

namespace App\Application\Staff\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class QuickCreateWaiterInput extends DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $pin,
        public ?string $waiterCommissionPercent,
        public ?string $notes,
    ) {
    }
}
