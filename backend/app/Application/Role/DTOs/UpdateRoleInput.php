<?php

declare(strict_types=1);

namespace App\Application\Role\DTOs;

final readonly class UpdateRoleInput
{
    public function __construct(
        public string $name,
        public string $slug,
    ) {
    }
}
