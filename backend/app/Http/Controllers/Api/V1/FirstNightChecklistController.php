<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\GetFirstNightChecklistUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class FirstNightChecklistController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetFirstNightChecklistUseCase $checklist,
    ) {
    }

    public function show(): JsonResponse
    {
        return $this->presenter->present($this->checklist->execute());
    }
}
