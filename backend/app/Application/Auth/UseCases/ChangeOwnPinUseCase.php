<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\ChangeOwnPinInput;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Application\Support\AuditLogRecorder;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Hash;

final class ChangeOwnPinUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLogRecorder $audit,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof ChangeOwnPinInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $passwordHash = $this->users->getPasswordHashById($input->userId);

        if ($passwordHash === null || ! Hash::check($input->currentPassword, $passwordHash)) {
            throw InvalidCredentialsException::create();
        }

        $this->users->updatePinById($input->userId, $input->newPin);

        $this->audit->recordForUser(
            tenantId: $input->tenantId,
            branchId: $input->branchId,
            userId: $input->userId,
            action: 'USER_PIN_CHANGED',
            subjectType: 'user',
            subjectId: $input->userId,
        );

        return OperationResult::ok('PIN actualizado correctamente.');
    }
}
