<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\ChangeOwnPasswordInput;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Hash;

final class ChangeOwnPasswordUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuthRepositoryInterface $auth,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof ChangeOwnPasswordInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $passwordHash = $this->users->getPasswordHashById($input->userId);

        if ($passwordHash === null || ! Hash::check($input->currentPassword, $passwordHash)) {
            throw InvalidCredentialsException::create();
        }

        $this->users->updatePasswordById($input->userId, $input->newPassword);
        $this->auth->invalidateCurrentToken();

        $this->audit->recordForUser(
            tenantId: $input->tenantId,
            branchId: $input->branchId,
            userId: $input->userId,
            action: 'USER_PASSWORD_CHANGED',
            subjectType: 'user',
            subjectId: $input->userId,
        );

        return OperationResult::ok('Contraseña actualizada correctamente.');
    }
}
