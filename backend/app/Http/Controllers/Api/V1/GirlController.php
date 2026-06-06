<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Girl\UseCases\GetGirlShiftEarningsUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class GirlController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetGirlShiftEarningsUseCase $shiftEarnings,
    ) {
    }

    public function shiftEarnings(): JsonResponse
    {
        return $this->presenter->present($this->shiftEarnings->execute());
    }
}
