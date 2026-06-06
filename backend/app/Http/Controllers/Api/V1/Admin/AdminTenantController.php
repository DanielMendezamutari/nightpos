<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Tenant\DTOs\CreateTenantInput;
use App\Application\Tenant\DTOs\UpdateTenantInput;
use App\Application\Tenant\UseCases\CreateTenantAdminUseCase;
use App\Application\Tenant\UseCases\GetTenantAdminUseCase;
use App\Application\Tenant\UseCases\ListTenantsAdminUseCase;
use App\Application\Tenant\UseCases\UpdateTenantAdminUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\CreateTenantRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTenantRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class AdminTenantController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListTenantsAdminUseCase $listTenants,
        private readonly CreateTenantAdminUseCase $createTenant,
        private readonly GetTenantAdminUseCase $getTenant,
        private readonly UpdateTenantAdminUseCase $updateTenant,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listTenants->execute());
    }

    public function store(CreateTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createTenant->execute(new CreateTenantInput(
            name: $validated['name'],
            slug: $validated['slug'],
            status: $validated['status'] ?? 'active',
            planName: $validated['plan_name'] ?? null,
            subscriptionStartsAt: isset($validated['subscription_starts_at'])
                ? new \DateTimeImmutable($validated['subscription_starts_at'])
                : null,
            subscriptionEndsAt: isset($validated['subscription_ends_at'])
                ? new \DateTimeImmutable($validated['subscription_ends_at'])
                : null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getTenant->execute((object) ['tenantId' => $id]));
    }

    public function update(int $id, UpdateTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateTenant->execute(new UpdateTenantInput(
            tenantId: $id,
            name: $validated['name'],
            slug: $validated['slug'],
            status: $validated['status'],
            planName: $validated['plan_name'] ?? null,
            subscriptionStartsAt: isset($validated['subscription_starts_at'])
                ? new \DateTimeImmutable($validated['subscription_starts_at'])
                : null,
            subscriptionEndsAt: isset($validated['subscription_ends_at'])
                ? new \DateTimeImmutable($validated['subscription_ends_at'])
                : null,
        ));

        return $this->presenter->present($result);
    }
}
