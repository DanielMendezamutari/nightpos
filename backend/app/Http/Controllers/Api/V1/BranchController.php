<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Branch\UseCases\GetCurrentBranchUseCase;
use App\Application\Branch\UseCases\ListAvailableBranchesUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class BranchController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentBranchUseCase $getCurrentBranch,
        private readonly ListAvailableBranchesUseCase $listAvailableBranches,
    ) {
    }

    public function current(): JsonResponse
    {
        return $this->presenter->present($this->getCurrentBranch->execute());
    }

    public function available(): JsonResponse
    {
        return $this->presenter->present($this->listAvailableBranches->execute());
    }
}
