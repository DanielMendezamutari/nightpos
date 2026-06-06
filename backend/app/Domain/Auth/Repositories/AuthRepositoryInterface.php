<?php

declare(strict_types=1);

namespace App\Domain\Auth\Repositories;

use App\Shared\Contracts\RepositoryInterface;

/**
 * Port for authentication token lifecycle (JWT).
 */
interface AuthRepositoryInterface extends RepositoryInterface
{
    public function issueTokenForUserId(int $userId): string;

    public function invalidateCurrentToken(): void;
}
