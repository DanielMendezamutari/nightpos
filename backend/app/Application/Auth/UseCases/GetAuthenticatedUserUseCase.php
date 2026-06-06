<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\Services\AuthResponseMapper;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class GetAuthenticatedUserUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuthResponseMapper $mapper,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $userId = $input instanceof \stdClass && isset($input->userId)
            ? (int) $input->userId
            : null;

        if ($userId === null) {
            return OperationResult::fail('Usuario no autenticado.');
        }

        $user = $this->users->findById($userId);

        if ($user === null) {
            return OperationResult::fail('Usuario no encontrado.');
        }

        return OperationResult::ok('Usuario autenticado.', [
            'user' => $this->mapper->toTokenOutput($user, '')->user,
        ]);
    }
}
