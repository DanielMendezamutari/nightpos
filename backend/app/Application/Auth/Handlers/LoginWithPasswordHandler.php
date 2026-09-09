<?php

declare(strict_types=1);

namespace App\Application\Auth\Handlers;

use App\Application\Auth\Commands\LoginWithPasswordCommand;
use App\Application\Auth\DTOs\AuthResponseDTO;
use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

final class LoginWithPasswordHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenGeneratorInterface $tokenGenerator
    ) {}

    public function handle(LoginWithPasswordCommand $command): AuthResponseDTO
    {
        $tenant = TenantModel::query()
            ->where('slug', $command->tenantSlug)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            throw new RuntimeException('Restaurante no encontrado o inactivo.');
        }

        $userModel = UserModel::query()
            ->where('tenant_id', $tenant->id)
            ->where('username', $command->username)
            ->where('is_active', true)
            ->first();

        if (!$userModel || !Hash::check($command->password, $userModel->password)) {
            throw new RuntimeException('Credenciales invalidas para administracion.');
        }

        $user = $this->userRepository->findById((string) $userModel->id);
        if (!$user) {
            throw new RuntimeException('Usuario no encontrado.');
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
