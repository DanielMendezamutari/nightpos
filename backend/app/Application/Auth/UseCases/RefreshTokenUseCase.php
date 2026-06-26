<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RefreshTokenUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly AuthRepositoryInterface $auth,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        try {
            $token = $this->auth->refreshCurrentToken();

            Log::info('auth.refresh.success', [
                'user_id' => auth('api')->id(),
            ]);

            return OperationResult::ok('Token renovado.', [
                'token' => $token,
                'token_type' => 'bearer',
            ]);
        } catch (Throwable $exception) {
            Log::warning('auth.refresh.failed', [
                'reason' => class_basename($exception),
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
