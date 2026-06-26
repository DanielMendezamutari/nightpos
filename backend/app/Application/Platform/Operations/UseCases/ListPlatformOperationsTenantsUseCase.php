<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\UseCases;

use App\Application\Platform\Operations\Support\PlatformOperationsAccessGuard;
use App\Application\Platform\Operations\Support\PlatformOperationsDashboardBuilder;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListPlatformOperationsTenantsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly PlatformOperationsAccessGuard $access,
        private readonly PlatformOperationsDashboardBuilder $builder,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorize();

        $filters = [
            'status' => $input->status ?? null,
            'health' => $input->health ?? null,
            'agent_offline' => filter_var($input->agentOffline ?? false, FILTER_VALIDATE_BOOL),
            'no_sales_today' => filter_var($input->noSalesToday ?? false, FILTER_VALIDATE_BOOL),
            'open_cash_too_long' => filter_var($input->openCashTooLong ?? false, FILTER_VALIDATE_BOOL),
            'print_errors' => filter_var($input->printErrors ?? false, FILTER_VALIDATE_BOOL),
            'search' => $input->search ?? null,
        ];

        return OperationResult::ok('Tenants operativos.', [
            'items' => $this->builder->buildTenantList($filters),
        ]);
    }
}
