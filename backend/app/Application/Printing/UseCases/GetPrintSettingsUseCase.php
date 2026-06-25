<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Printing\Services\BranchPrintSettingsReader;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetPrintSettingsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly BranchPrintSettingsReader $branchSettings,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $deviceRows = $this->devices->listByBranch($tenant->id, $branch->id);
        $devices = array_map(function (array $device) use ($tenant, $branch): array {
            $device['job_summary'] = $this->jobs->deviceJobSummary(
                $tenant->id,
                $branch->id,
                (int) $device['id'],
            );

            return $device;
        }, $deviceRows);

        return OperationResult::ok('Configuración de impresión.', [
            'auto_print_order_command' => $this->branchSettings->isAutoPrintOrderCommandEnabled($branch->id),
            'auto_print_sale_receipt' => $this->branchSettings->isAutoPrintSaleReceiptEnabled($branch->id),
            'devices' => $devices,
            'queue_summary' => $this->jobs->branchQueueSummary($tenant->id, $branch->id),
        ]);
    }
}
