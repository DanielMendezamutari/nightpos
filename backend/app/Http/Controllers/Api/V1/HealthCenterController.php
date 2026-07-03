<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Health\UseCases\GetTenantHealthSummaryUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class HealthCenterController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetTenantHealthSummaryUseCase $tenantSummary,
    ) {
    }

    public function summary(): JsonResponse
    {
        return $this->presenter->present($this->tenantSummary->execute());
    }
}
