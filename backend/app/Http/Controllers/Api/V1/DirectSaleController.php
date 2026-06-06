<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Sale\DTOs\DirectSaleInput;
use App\Application\Sale\UseCases\CreateDirectSaleUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Sale\DirectSaleRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class DirectSaleController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly CreateDirectSaleUseCase $createDirectSale,
    ) {
    }

    public function store(DirectSaleRequest $request): JsonResponse
    {
        return $this->presenter->present(
            $this->createDirectSale->execute(new DirectSaleInput(
                items: $request->input('items', []),
                payments: $request->input('payments', []),
                notes: $request->input('notes'),
            )),
            201,
        );
    }
}
