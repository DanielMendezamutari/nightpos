<?php

declare(strict_types=1);

namespace App\Application\Reports\UseCases;

use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetSalesReportUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ReportReadRepositoryInterface $reports,
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $filters = $this->extractFilters($input);
        $data    = $this->reports->getSalesReport($tenant->id, $branch->id, $filters);

        return OperationResult::ok('Reporte de ventas.', $data);
    }

    private function extractFilters(?object $input): array
    {
        if ($input === null) {
            return [];
        }

        $filters = [];

        foreach (['date_from', 'date_to', 'official_shift_id', 'cashier_user_id', 'waiter_user_id', 'payment_method'] as $key) {
            if (isset($input->{$key})) {
                $filters[$key] = $input->{$key};
            }
        }

        return $filters;
    }
}
