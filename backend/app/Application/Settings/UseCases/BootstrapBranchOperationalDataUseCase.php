<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Application\Settings\Services\BranchOperationalBootstrapService;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class BootstrapBranchOperationalDataUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly BranchOperationalBootstrapService $bootstrapService,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $created = $this->bootstrapService->bootstrap($tenant->id, $branch->id);

        return OperationResult::ok('Datos operativos iniciales aplicados.', [
            'created' => $created,
            'skipped' => $created === [],
        ]);
    }
}
