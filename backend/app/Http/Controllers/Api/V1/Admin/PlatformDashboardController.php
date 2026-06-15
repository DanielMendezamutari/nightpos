<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Application\Platform\UseCases\GetPlatformDashboardUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class PlatformDashboardController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetPlatformDashboardUseCase $dashboard,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->dashboard->execute());
    }
}
