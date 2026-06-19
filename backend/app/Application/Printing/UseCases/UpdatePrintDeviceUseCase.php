<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdatePrintDeviceUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PrintDeviceRepositoryInterface $devices,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $deviceId = (int) ($input->deviceId ?? 0);
        $fields = [];

        if (isset($input->name)) {
            $fields['name'] = trim((string) $input->name);
        }
        if (isset($input->enabled)) {
            $fields['enabled'] = (bool) $input->enabled;
        }
        if (isset($input->autoPrintOrder)) {
            $fields['auto_print_order'] = (bool) $input->autoPrintOrder;
        }
        if (isset($input->paperWidthMm)) {
            $fields['paper_width_mm'] = (int) $input->paperWidthMm;
        }
        if (isset($input->status)) {
            $fields['status'] = (string) $input->status;
        }

        $updated = $this->devices->update($deviceId, $tenant->id, $branch->id, $fields);

        if ($updated === null) {
            throw PrintingDomainException::jobNotFound();
        }

        return OperationResult::ok('Dispositivo actualizado.', [
            'device' => $updated,
        ]);
    }
}
