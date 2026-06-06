<?php

declare(strict_types=1);

namespace App\Application\Staff\DTOs;

final readonly class QuickCreateGirlInput
{
    public function __construct(
        public string $name,
        public ?string $pin = null,
        public ?string $notes = null,
    ) {
    }
}
