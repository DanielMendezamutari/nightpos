<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Health\UseCases\GetPlatformHealthSummaryUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class HealthCenterController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetPlatformHealthSummaryUseCase $platformSummary,
    ) {
    }

    public function platformSummary(): JsonResponse
    {
        return $this->presenter->present($this->platformSummary->execute());
    }
}
