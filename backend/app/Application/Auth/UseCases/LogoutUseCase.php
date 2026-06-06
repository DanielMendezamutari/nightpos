<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class LogoutUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $auth,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $this->auth->invalidateCurrentToken();

        return OperationResult::ok('Sesión cerrada correctamente.');
    }
}
