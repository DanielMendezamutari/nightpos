<?php

declare(strict_types=1);

namespace App\Application\Platform\UseCases;

use App\Application\Platform\DTOs\PlatformSetupInput;
use App\Application\Tenant\DTOs\TenantProvisionInput;
use App\Application\Tenant\Support\TenantProvisioner;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class PlatformSetupUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantProvisioner $provisioner,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof PlatformSetupInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $result = $this->provisioner->provision(new TenantProvisionInput(
            tenantName: $input->tenantName,
            tenantSlug: $input->tenantSlug,
            tenantStatus: $input->tenantStatus,
            planId: $input->planId,
            planName: $input->planName,
            subscriptionStartsAt: null,
            subscriptionEndsAt: null,
            branchName: $input->branchName,
            branchCode: $input->branchCode,
            branchAddress: $input->branchAddress,
            branchStatus: $input->branchStatus,
            adminName: $input->adminName,
            adminUsername: $input->adminUsername,
            adminEmail: $input->adminEmail,
            adminPassword: $input->adminPassword,
            adminPin: $input->adminPin,
        ));

        return OperationResult::ok('Empresa operativa creada correctamente.', $result);
    }
}
