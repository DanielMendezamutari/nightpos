<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\ShiftConsole\UseCases\GetCurrentShiftConsoleUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class ShiftConsoleController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentShiftConsoleUseCase $current,
    ) {
    }

    public function current(): JsonResponse
    {
        return $this->presenter->present($this->current->execute());
    }
}
