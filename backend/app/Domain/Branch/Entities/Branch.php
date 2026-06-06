<?php

declare(strict_types=1);

namespace App\Domain\Branch\Entities;

final readonly class Branch
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public string $name,
        public string $code,
        public ?string $address,
        public string $status,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
