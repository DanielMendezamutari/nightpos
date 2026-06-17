<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class RefreshTokenUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $auth,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $token = $this->auth->refreshCurrentToken();

        return OperationResult::ok('Token renovado.', [
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }
}
