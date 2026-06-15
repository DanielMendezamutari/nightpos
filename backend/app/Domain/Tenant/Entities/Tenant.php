<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Entities;

final readonly class Tenant
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $status,
        public ?int $planId,
        public ?string $planName,
        public ?\DateTimeImmutable $subscriptionStartsAt,
        public ?\DateTimeImmutable $subscriptionEndsAt,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasValidSubscription(?\DateTimeImmutable $at = null): bool
    {
        $at ??= new \DateTimeImmutable;

        if ($this->subscriptionEndsAt === null) {
            return true;
        }

        return $at <= $this->subscriptionEndsAt;
    }
}
