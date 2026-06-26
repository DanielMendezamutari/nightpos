<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class PrintDeviceHeartbeatUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly RequestPrintDeviceContext $deviceContext,
        private readonly PrintDeviceRepositoryInterface $devices,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $device = $this->deviceContext->device();

        if ($device === null) {
            return OperationResult::fail('Dispositivo no autenticado.');
        }

        $this->devices->recordHeartbeat(
            id: (int) $device['id'],
            tenantId: (int) $device['tenant_id'],
            branchId: (int) $device['branch_id'],
            printerName: isset($input->printerName) ? (string) $input->printerName : null,
            agentVersion: isset($input->agentVersion) ? (string) $input->agentVersion : null,
            lastError: isset($input->lastError) ? (string) $input->lastError : null,
            hostName: isset($input->hostName) ? (string) $input->hostName : null,
            osName: isset($input->osName) ? (string) $input->osName : null,
            osVersion: isset($input->osVersion) ? (string) $input->osVersion : null,
            arch: isset($input->arch) ? (string) $input->arch : null,
            ipAddress: isset($input->ipAddress) ? (string) $input->ipAddress : null,
            printerModel: isset($input->printerModel) ? (string) $input->printerModel : null,
        );

        return OperationResult::ok('Heartbeat registrado.');
    }
}
