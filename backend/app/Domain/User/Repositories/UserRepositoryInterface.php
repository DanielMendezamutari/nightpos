<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;

interface UserRepositoryInterface
{
    public function findByPin(string $tenantId, ?string $branchCode, string $plainPin): ?User;
    public function findByUsername(string $tenantId, string $username): ?User;
    public function findById(string $id): ?User;
}
