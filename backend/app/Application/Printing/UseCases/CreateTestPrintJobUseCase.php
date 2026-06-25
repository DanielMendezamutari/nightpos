<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateTestPrintJobUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
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
        if ($deviceId <= 0) {
            throw PrintingDomainException::jobNotFound();
        }

        $device = $this->devices->findById($deviceId, $tenant->id, $branch->id);
        if ($device === null) {
            throw PrintingDomainException::jobNotFound();
        }

        if (! ($device['enabled'] ?? false)) {
            throw PrintingDomainException::deviceDisabled();
        }

        $printedAt = now()->format('d/m/Y H:i:s');
        $contentText = implode("\n", [
            '================================',
            '     NightPOS — PRUEBA',
            '================================',
            '',
            'Dispositivo: '.($device['name'] ?? '—'),
            'Sucursal ID: '.$branch->id,
            'Fecha: '.$printedAt,
            '',
            'Si ve este ticket, el agente',
            'y la impresora estan OK.',
            '',
            '================================',
            '',
        ]);

        $job = $this->jobs->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'device_id' => null,
            'type' => PrintJobType::Test->value,
            'source_type' => PrintJobSourceType::PrintDevice->value,
            'source_id' => $deviceId,
            'idempotency_key' => 'test:'.$deviceId.':'.now()->format('YmdHisv'),
            'payload' => [
                'device_id' => $deviceId,
                'device_name' => $device['name'] ?? null,
                'printed_at' => $printedAt,
            ],
            'content_text' => $contentText,
            'status' => PrintJobStatus::Pending->value,
            'requested_by_user_id' => $this->staffContext->userId(),
        ]);

        return OperationResult::ok('Trabajo de prueba encolado.', [
            'job' => $job,
        ]);
    }
}
