<?php

declare(strict_types=1);

namespace App\Application\Role\DTOs;

final readonly class CreateRoleInput
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {
    }
}
