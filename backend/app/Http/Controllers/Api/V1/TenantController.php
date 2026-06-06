<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Tenant\UseCases\GetCurrentTenantUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class TenantController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentTenantUseCase $getCurrentTenant,
    ) {
    }

    public function current(): JsonResponse
    {
        return $this->presenter->present($this->getCurrentTenant->execute());
    }
}
