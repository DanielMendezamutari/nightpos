<?php

declare(strict_types=1);

namespace App\Domain\Auth\Ports;

use App\Domain\User\Entities\User;

interface TokenGeneratorInterface
{
    public function generateForUser(User $user): string;
}
