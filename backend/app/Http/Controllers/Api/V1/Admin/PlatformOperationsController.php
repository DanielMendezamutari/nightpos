<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Platform\Operations\UseCases\GetPlatformOperationsChecklistUseCase;
use App\Application\Platform\Operations\UseCases\GetPlatformOperationsDashboardUseCase;
use App\Application\Platform\Operations\UseCases\GetPlatformOperationsTechnicalProfileUseCase;
use App\Application\Platform\Operations\UseCases\GetPlatformOperationsTenantUseCase;
use App\Application\Platform\Operations\UseCases\ListPlatformOperationsPrintAgentsUseCase;
use App\Application\Platform\Operations\UseCases\ListPlatformOperationsTenantsUseCase;
use App\Application\Platform\Operations\UseCases\PatchPlatformOperationsChecklistItemUseCase;
use App\Application\Platform\Operations\UseCases\UpdatePlatformOperationsTechnicalProfileUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Platform\PatchPlatformOperationsChecklistRequest;
use App\Http\Requests\Api\V1\Platform\UpdatePlatformOperationsTechnicalProfileRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlatformOperationsController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetPlatformOperationsDashboardUseCase $dashboard,
        private readonly ListPlatformOperationsTenantsUseCase $listTenants,
        private readonly GetPlatformOperationsTenantUseCase $getTenant,
        private readonly ListPlatformOperationsPrintAgentsUseCase $listPrintAgents,
        private readonly GetPlatformOperationsTechnicalProfileUseCase $getTechnicalProfile,
        private readonly UpdatePlatformOperationsTechnicalProfileUseCase $updateTechnicalProfile,
        private readonly GetPlatformOperationsChecklistUseCase $getChecklist,
        private readonly PatchPlatformOperationsChecklistItemUseCase $patchChecklistItem,
    ) {
    }

    public function dashboard(): JsonResponse
    {
        return $this->presenter->present($this->dashboard->execute());
    }

    public function tenants(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listTenants->execute((object) [
            'status' => $request->query('status'),
            'health' => $request->query('health'),
            'agentOffline' => $request->query('agent_offline'),
            'noSalesToday' => $request->query('no_sales_today'),
            'openCashTooLong' => $request->query('open_cash_too_long'),
            'printErrors' => $request->query('print_errors'),
            'search' => $request->query('search'),
        ]));
    }

    public function showTenant(int $tenantId): JsonResponse
    {
        return $this->presenter->present($this->getTenant->execute((object) [
            'tenantId' => $tenantId,
        ]));
    }

    public function printAgents(): JsonResponse
    {
        return $this->presenter->present($this->listPrintAgents->execute());
    }

    public function showTechnicalProfile(int $tenantId): JsonResponse
    {
        return $this->presenter->present($this->getTechnicalProfile->execute((object) [
            'tenantId' => $tenantId,
        ]));
    }

    public function updateTechnicalProfile(int $tenantId, UpdatePlatformOperationsTechnicalProfileRequest $request): JsonResponse
    {
        return $this->presenter->present($this->updateTechnicalProfile->execute((object) [
            'tenantId' => $tenantId,
            'data' => $request->validated(),
        ]));
    }

    public function checklist(int $tenantId): JsonResponse
    {
        return $this->presenter->present($this->getChecklist->execute((object) [
            'tenantId' => $tenantId,
        ]));
    }

    public function patchChecklistItem(int $tenantId, string $key, PatchPlatformOperationsChecklistRequest $request): JsonResponse
    {
        return $this->presenter->present($this->patchChecklistItem->execute((object) [
            'tenantId' => $tenantId,
            'key' => $key,
            'payload' => $request->validated(),
        ]));
    }
}
