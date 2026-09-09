<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LoginWithPinCommand;
use App\Application\Auth\DTOs\AuthResponseDTO;
use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\ValueObjects\Pin;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use RuntimeException;

final class LoginWithPinHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator
    ) {}

    public function handle(LoginWithPinCommand $command): AuthResponseDTO
    {
        $pinVo = new Pin($command->pin);

        $tenant = TenantModel::query()
            ->where('slug', $command->tenantSlug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            throw new RuntimeException('Restaurante no encontrado o inactivo.');
        }

        $user = $this->userRepository->findByPin((string) $tenant->id, $command->branchCode, $pinVo->value());

        if (!$user) {
            throw new RuntimeException('PIN incorrecto o no asignado a este personal.');
        }

        if (!$user->isActive()) {
            throw new RuntimeException('Cuenta de usuario inactiva.');
        }

        $token = $this->tokenGenerator->generateForUser($user);

        return new AuthResponseDTO(
            token: $token,
            tokenType: 'bearer',
            user: [
                'id' => $user->id(),
                'name' => $user->name(),
                'username' => $user->username(),
                'email' => $user->email(),
                'role' => $user->role(),
                'tenant_id' => $user->tenantId(),
                'branch_id' => $user->branchId(),
            ]
        );
    }
}
