<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Platform\DTOs\PlatformSetupInput;
use App\Application\Platform\UseCases\PlatformSetupUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\PlatformSetupRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class PlatformSetupController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly PlatformSetupUseCase $platformSetup,
    ) {
    }

    public function store(PlatformSetupRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->platformSetup->execute(new PlatformSetupInput(
            tenantName: $validated['tenant']['name'],
            tenantSlug: $validated['tenant']['slug'],
            tenantStatus: $validated['tenant']['status'] ?? 'active',
            planId: isset($validated['tenant']['plan_id']) ? (int) $validated['tenant']['plan_id'] : null,
            planName: $validated['tenant']['plan_name'] ?? null,
            branchName: $validated['branch']['name'],
            branchCode: $validated['branch']['code'],
            branchAddress: $validated['branch']['address'] ?? null,
            branchStatus: $validated['branch']['status'] ?? 'active',
            adminName: $validated['admin']['name'],
            adminUsername: $validated['admin']['username'],
            adminEmail: $validated['admin']['email'] ?? null,
            adminPassword: $validated['admin']['password'],
            adminPin: $validated['admin']['pin'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }
}
