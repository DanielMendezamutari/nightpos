<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Application\Platform\Operations\Support\PlatformOperationsMetricsReader;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListPlatformOperationsPrintAgentsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
        private readonly PlatformOperationsMetricsReader $metrics,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        return OperationResult::ok('Agentes de impresión.', [
            'items' => $this->metrics->allPrintAgents(),
        ]);
    }
}
