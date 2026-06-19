<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Printing\Services\PrintDeviceKeyService;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class RegisterPrintDeviceUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintDeviceKeyService $keyService,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $name = trim((string) ($input->name ?? ''));
        if ($name === '') {
            return OperationResult::fail('Indique el nombre del dispositivo.');
        }

        $existing = $this->devices->listByBranch($tenant->id, $branch->id);
        foreach ($existing as $device) {
            if (strcasecmp((string) $device['name'], $name) === 0) {
                throw PrintingDomainException::deviceNameTaken();
            }
        }

        $paperWidth = (int) ($input->paperWidthMm ?? 80);
        $autoPrint = (bool) ($input->autoPrintOrder ?? true);
        $key = $this->keyService->generate();

        $device = $this->devices->create(
            tenantId: $tenant->id,
            branchId: $branch->id,
            name: $name,
            deviceKeyHash: $key['hash'],
            deviceKeyPrefix: $key['prefix'],
            paperWidthMm: $paperWidth,
            autoPrintOrder: $autoPrint,
        );

        return OperationResult::ok('Dispositivo registrado. Guarde la clave; no se volverá a mostrar.', [
            'device' => $device,
            'device_key' => $key['plaintext'],
        ]);
    }
}
