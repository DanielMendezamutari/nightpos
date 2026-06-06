<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\BootstrapBranchOperationalDataUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class SettingsBootstrapController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly BootstrapBranchOperationalDataUseCase $bootstrap,
    ) {
    }

    public function store(): JsonResponse
    {
        return $this->presenter->present($this->bootstrap->execute(), 201);
    }
}
