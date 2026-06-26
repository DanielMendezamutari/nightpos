<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Application\Platform\Operations\Support\PlatformOperationsDashboardBuilder;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class GetPlatformOperationsTenantUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
        private readonly PlatformOperationsDashboardBuilder $builder,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        $tenantId = (int) ($input->tenantId ?? 0);
        $detail = $this->builder->buildTenantDetail($tenantId);

        if ($detail === null) {
            return OperationResult::fail('Tenant no encontrado.');
        }

        return OperationResult::ok('Detalle operativo tenant.', $detail);
    }
}
