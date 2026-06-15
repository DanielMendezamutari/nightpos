<?php

declare(strict_types=1);

namespace App\Application\Role\DTOs;

final readonly class UpdateRolePermissionsInput
{
    /**
     * @param list<string> $permissionSlugs
     */
    public function __construct(
        public array $permissionSlugs,
    ) {
    }
}
