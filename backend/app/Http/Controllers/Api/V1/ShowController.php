<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\GirlIncome\DTOs\CreateShowInput;
use App\Application\GirlIncome\UseCases\CreateShowUseCase;
use App\Application\GirlIncome\UseCases\GetShowUseCase;
use App\Application\GirlIncome\UseCases\ListCurrentShiftShowsUseCase;
use App\Application\Printing\UseCases\PrintShowUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GirlIncome\CreateShowRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ShowController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListCurrentShiftShowsUseCase $listCurrent,
        private readonly CreateShowUseCase $create,
        private readonly GetShowUseCase $get,
        private readonly PrintShowUseCase $printShow,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listCurrent->execute());
    }

    public function store(CreateShowRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->create->execute(new CreateShowInput(
            girlUserId: (int) $validated['girl_user_id'],
            showType: $validated['show_type'],
            unitPrice: (string) $validated['unit_price'],
            paymentMethod: (string) $validated['payment_method'],
            registeredAt: $validated['registered_at'] ?? null,
            notes: $validated['notes'] ?? null,
        )), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->get->execute((object) ['showId' => $id]));
    }

    public function print(int $id): JsonResponse
    {
        $reprint = (bool) request()->boolean('reprint');

        return $this->presenter->present($this->printShow->execute((object) [
            'showId' => $id,
            'reprint' => $reprint,
        ]));
    }
}
