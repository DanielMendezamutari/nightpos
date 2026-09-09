<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

final class AuthResponseDTO
{
    public function __construct(
        public readonly string $token,
        public readonly string $tokenType,
        public readonly array $user
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => $this->tokenType,
            'user' => $this->user,
        ];
    }
}
