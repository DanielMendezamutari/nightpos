<?php

declare(strict_types=1);

namespace App\Application\Plan\UseCases;

use App\Application\Plan\Support\PlanAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListPlansUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.list');
        }

        $plans = PlanModel::query()
            ->withCount('tenants')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (PlanModel $plan) => PlanAdminMapper::plan($plan, (int) $plan->tenants_count))
            ->all();

        return OperationResult::ok('Listado de planes.', ['plans' => $plans]);
    }
}
