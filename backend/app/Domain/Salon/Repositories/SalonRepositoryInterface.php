<?php

declare(strict_types=1);

namespace App\Domain\Salon\Repositories;

use App\Domain\Salon\Entities\Salon;

interface SalonRepositoryInterface
{
    /**
     * @return Salon[]
     */
    public function getSalonesByBranch(string $tenantId, string $branchId): array;

    public function findById(int $id): ?Salon;
}