<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Application\Platform\Operations\Support\PlatformOperationsChecklistService;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetPlatformOperationsChecklistUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
        private readonly PlatformOperationsChecklistService $checklist,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        $tenantId = (int) ($input->tenantId ?? 0);

        return OperationResult::ok('Checklist instalación.', [
            'items' => $this->checklist->listForTenant($tenantId),
        ]);
    }
}
