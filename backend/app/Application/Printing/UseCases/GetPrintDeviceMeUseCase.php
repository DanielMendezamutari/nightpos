<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class GetPrintDeviceMeUseCase implements UseCaseInterface
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

        $fresh = $this->devices->findById(
            (int) $device['id'],
            (int) $device['tenant_id'],
            (int) $device['branch_id'],
        );

        return OperationResult::ok('Dispositivo autenticado.', [
            'device' => $fresh ?? $device,
        ]);
    }
}
