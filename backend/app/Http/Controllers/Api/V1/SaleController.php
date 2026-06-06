<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Application\Sale\DTOs\GetSaleInput;
use App\Application\Sale\UseCases\GetSaleUseCase;
use App\Application\Sale\UseCases\ListSalesUseCase;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SaleController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListSalesUseCase $listSales,
        private readonly GetSaleUseCase $getSale,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $currentSessionOnly = filter_var($request->query('current_session', true), FILTER_VALIDATE_BOOLEAN);
        $currentShiftOnly = filter_var($request->query('current_shift', false), FILTER_VALIDATE_BOOLEAN);

        return $this->presenter->present($this->listSales->execute((object) [
            'currentSessionOnly' => $currentSessionOnly,
            'currentShiftOnly' => $currentShiftOnly,
        ]));
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSale->execute(new GetSaleInput($id)));
    }
}
