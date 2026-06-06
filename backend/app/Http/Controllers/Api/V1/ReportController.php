<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Reports\UseCases\GetCashReportUseCase;
use App\Application\Reports\UseCases\GetDailyReportUseCase;
use App\Application\Reports\UseCases\GetProductReconciliationReportUseCase;
use App\Application\Reports\UseCases\GetRoomsReportUseCase;
use App\Application\Reports\UseCases\GetSalesReportUseCase;
use App\Application\Reports\UseCases\GetServicesReportUseCase;
use App\Application\Reports\UseCases\GetSettlementsReportUseCase;
use App\Application\Reports\UseCases\GetShiftClosureCheckUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetDailyReportUseCase $daily,
        private readonly GetSalesReportUseCase $sales,
        private readonly GetCashReportUseCase $cash,
        private readonly GetServicesReportUseCase $services,
        private readonly GetSettlementsReportUseCase $settlements,
        private readonly GetRoomsReportUseCase $rooms,
        private readonly GetShiftClosureCheckUseCase $closureCheck,
        private readonly GetProductReconciliationReportUseCase $productReconciliation,
    ) {}

    public function daily(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->daily->execute($this->filtersFromRequest($request))
        );
    }

    public function sales(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->sales->execute($this->filtersFromRequest($request, ['cashier_user_id', 'waiter_user_id', 'payment_method']))
        );
    }

    public function cash(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->cash->execute($this->filtersFromRequest($request))
        );
    }

    public function services(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->services->execute($this->filtersFromRequest($request, ['girl_user_id']))
        );
    }

    public function settlements(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->settlements->execute($this->filtersFromRequest($request))
        );
    }

    public function rooms(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->rooms->execute($this->filtersFromRequest($request))
        );
    }

    public function shiftClosure(): JsonResponse
    {
        return $this->presenter->present($this->closureCheck->execute());
    }

    public function productReconciliation(Request $request): JsonResponse
    {
        return $this->presenter->present(
            $this->productReconciliation->execute(
                $this->filtersFromRequest($request, ['cash_session_id', 'waiter_user_id'])
            )
        );
    }

    private function filtersFromRequest(Request $request, array $extra = []): object
    {
        $keys = array_merge(['date_from', 'date_to', 'official_shift_id'], $extra);
        $data = [];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $data[$key] = $request->input($key);
            }
        }

        return (object) $data;
    }
}
