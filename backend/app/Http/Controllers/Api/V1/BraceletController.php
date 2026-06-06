<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\GirlIncome\DTOs\CreateBraceletInput;
use App\Application\GirlIncome\UseCases\CreateBraceletUseCase;
use App\Application\GirlIncome\UseCases\GetBraceletUseCase;
use App\Application\GirlIncome\UseCases\ListCurrentShiftBraceletsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GirlIncome\CreateBraceletRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class BraceletController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListCurrentShiftBraceletsUseCase $listCurrent,
        private readonly CreateBraceletUseCase $create,
        private readonly GetBraceletUseCase $get,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listCurrent->execute());
    }

    public function store(CreateBraceletRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->create->execute(new CreateBraceletInput(
            girlUserId: (int) $validated['girl_user_id'],
            quantity: (int) $validated['quantity'],
            unitPrice: (string) $validated['unit_price'],
            paymentMethod: (string) $validated['payment_method'],
            waiterUserId: isset($validated['waiter_user_id']) ? (int) $validated['waiter_user_id'] : null,
            notes: $validated['notes'] ?? null,
        )), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->get->execute((object) ['braceletId' => $id]));
    }
}
