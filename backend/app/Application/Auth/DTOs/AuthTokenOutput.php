<?php

declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class AuthTokenOutput extends DataTransferObject
{
    /**
     * @param  array<string, mixed>  $user
     */
    public function __construct(
        public string $token,
        public string $tokenType,
        public array $user,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => $this->tokenType,
            'user' => $this->user,
        ];
    }
}
