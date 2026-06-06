<?php

declare(strict_types=1);

namespace App\Domain\Tenant\Repositories;

use App\Domain\Tenant\Entities\Tenant;
use App\Shared\Contracts\RepositoryInterface;

interface TenantRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    /** @return list<Tenant> */
    public function listAll(): array;

    public function create(
        string $name,
        string $slug,
        string $status,
        ?string $planName,
        ?\DateTimeImmutable $subscriptionStartsAt,
        ?\DateTimeImmutable $subscriptionEndsAt,
    ): Tenant;

    public function update(
        int $id,
        string $name,
        string $slug,
        string $status,
        ?string $planName,
        ?\DateTimeImmutable $subscriptionStartsAt,
        ?\DateTimeImmutable $subscriptionEndsAt,
    ): Tenant;

    public function slugExists(string $slug, ?int $exceptTenantId = null): bool;
}
