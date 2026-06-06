<?php

declare(strict_types=1);

namespace App\Domain\Reports\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface ReportReadRepositoryInterface extends RepositoryInterface
{
    public function getDailySummary(int $tenantId, int $branchId, array $filters): array;

    public function getSalesReport(int $tenantId, int $branchId, array $filters): array;

    public function getCashReport(int $tenantId, int $branchId, array $filters): array;

    public function getServicesReport(int $tenantId, int $branchId, array $filters): array;

    public function getSettlementsReport(int $tenantId, int $branchId, array $filters): array;

    public function getRoomsReport(int $tenantId, int $branchId, array $filters): array;

    public function getShiftClosureCheck(int $tenantId, int $branchId, int $officialShiftId): array;

    public function getProductReconciliation(int $tenantId, int $branchId, array $filters): array;
}
