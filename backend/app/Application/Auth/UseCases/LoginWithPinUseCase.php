<?php

declare(strict_types=1);

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\AuthTokenOutput;
use App\Application\Auth\DTOs\LoginWithPinInput;
use App\Application\Auth\Services\AuthResponseMapper;
use App\Application\Auth\Services\TenantAccessGuard;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\PinFingerprint;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\Hash;

final class LoginWithPinUseCase implements UseCaseInterface
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
        if (! $input instanceof LoginWithPinInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! preg_match('/^\d{4,6}$/', $input->pin)) {
            return OperationResult::fail('El PIN debe tener entre 4 y 6 dígitos.');
        }

        $tenant = $this->tenantGuard->resolveTenant($input->tenantId, $input->tenantSlug);
        $this->tenantGuard->assertTenantAvailable($tenant);

        $branch = $this->tenantGuard->resolveBranch($tenant, $input->branchId, $input->branchCode);
        $this->tenantGuard->assertBranchBelongsToTenant($branch, $tenant);

        $tenantId = $tenant?->id;
        $branchId = $branch?->id ?? $input->branchId;

        $branchCode = $branch === null ? $input->branchCode : null;
        $fingerprint = PinFingerprint::fromPlain($input->pin, (string) config('app.key'));

        $matchedUserId = $this->users->findUserIdByPinFingerprintInScope(
            $fingerprint,
            $tenantId,
            $branchId,
            $branchCode,
        );

        if ($matchedUserId !== null) {
            $pinHash = $this->users->getPinHashById($matchedUserId);

            if ($pinHash === null || ! Hash::check($input->pin, $pinHash)) {
                throw InvalidCredentialsException::create();
            }
        } else {
            $matchedUserId = null;

            foreach ($this->users->findCandidateIdsByPinScope($tenantId, $branchId, $branchCode) as $userId) {
                $pinHash = $this->users->getPinHashById($userId);

                if ($pinHash !== null && Hash::check($input->pin, $pinHash)) {
                    $matchedUserId = $userId;
                    break;
                }
            }

            if ($matchedUserId === null) {
                throw InvalidCredentialsException::create();
            }
        }

        $user = $this->users->findById($matchedUserId);

        if ($user === null || $user->status !== 'active' || ! $this->userCanAccessBranch($user, $branchId)) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->auth->issueTokenForUserId($matchedUserId);
        $this->users->recordLastLogin($matchedUserId);

        /** @var AuthTokenOutput $output */
        $output = $this->mapper->toTokenOutput($user, $token);

        return OperationResult::ok('Inicio de sesión con PIN correcto.', $output->toArray());
    }

    private function userCanAccessBranch(\App\Domain\User\Entities\AuthenticatedUser $user, ?int $branchId): bool
    {
        if ($branchId === null) {
            return true;
        }

        // Superadmin plataforma (sin tenant) puede operar en cualquier sucursal del contexto.
        if ($user->tenantId === null || $user->roleSlug === 'super_admin') {
            return true;
        }

        return in_array($branchId, $user->accessibleBranchIds, true);
    }
}
