<?php

declare(strict_types=1);

namespace App\Application\Health\UseCases;

use App\Application\Health\Services\HealthCenterEvaluator;
use App\Application\Health\Support\HealthAccessGuard;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class GetPlatformHealthSummaryUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly HealthAccessGuard $access,
        private readonly HealthCenterEvaluator $evaluator,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->access->authorizePlatform();

        return OperationResult::ok('Resumen de salud plataforma.', $this->evaluator->evaluatePlatform());
    }
}
