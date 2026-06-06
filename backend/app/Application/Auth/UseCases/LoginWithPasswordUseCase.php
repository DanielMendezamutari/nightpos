<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\AuthTokenOutput;
use App\Application\Auth\DTOs\LoginWithPasswordInput;
use App\Application\Auth\Services\AuthResponseMapper;
use App\Application\Auth\Services\TenantAccessGuard;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Hash;

final class LoginWithPasswordUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuthRepositoryInterface $auth,
        private readonly TenantAccessGuard $tenantGuard,
        private readonly AuthResponseMapper $mapper,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof LoginWithPasswordInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $username = strtolower(trim($input->username));

        if ($username === '') {
            throw InvalidCredentialsException::create();
        }

        $tenantSlug = $input->tenantSlug !== null && trim($input->tenantSlug) !== ''
            ? trim($input->tenantSlug)
            : null;

        $tenant = $this->tenantGuard->resolveTenant($input->tenantId, $tenantSlug);
        $this->tenantGuard->assertTenantAvailable($tenant);

        $user = $this->users->findByUsernameForLogin($tenant?->id, $username);

        if ($user === null && $tenant !== null) {
            $platformUser = $this->users->findByUsernameForLogin(null, $username);

            if ($platformUser !== null && $platformUser->tenantId === null) {
                $user = $platformUser;
                $tenant = null;
            }
        }

        if ($user === null || $user->status !== 'active') {
            throw InvalidCredentialsException::create();
        }

        if ($user->tenantId !== null && $tenant === null) {
            throw InvalidCredentialsException::create();
        }

        $passwordHash = $this->users->getPasswordHashById($user->id);

        if ($passwordHash === null || ! Hash::check($input->password, $passwordHash)) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->auth->issueTokenForUserId($user->id);
        $this->users->recordLastLogin($user->id);

        /** @var AuthTokenOutput $output */
        $output = $this->mapper->toTokenOutput($user, $token);

        return OperationResult::ok('Inicio de sesión correcto.', $output->toArray());
    }
}
