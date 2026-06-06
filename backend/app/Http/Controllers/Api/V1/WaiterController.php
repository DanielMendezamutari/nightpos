<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\ListServiceAreasUseCase;
use App\Application\Staff\UseCases\ListOperationalGirlsUseCase;
use App\Application\Waiter\UseCases\GetWaiterDashboardUseCase;
use App\Application\Waiter\UseCases\ListWaiterOrdersUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class WaiterController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetWaiterDashboardUseCase $dashboard,
        private readonly ListWaiterOrdersUseCase $listOrders,
        private readonly ListServiceAreasUseCase $listServiceAreas,
        private readonly ListOperationalGirlsUseCase $listGirls,
    ) {
    }

    public function dashboard(): JsonResponse
    {
        return $this->presenter->present($this->dashboard->execute());
    }

    public function orders(): JsonResponse
    {
        return $this->presenter->present($this->listOrders->execute());
    }

    public function activeOrders(): JsonResponse
    {
        request()->merge(['scope' => 'active']);

        return $this->presenter->present($this->listOrders->execute());
    }

    public function serviceAreas(): JsonResponse
    {
        return $this->presenter->present($this->listServiceAreas->execute());
    }

    public function girls(): JsonResponse
    {
        return $this->presenter->present($this->listGirls->execute());
    }
}
