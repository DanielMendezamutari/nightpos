<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Auth\DTOs\ChangeOwnPasswordInput;
use App\Application\Auth\DTOs\ChangeOwnPinInput;
use App\Application\Auth\DTOs\LoginWithPasswordInput;
use App\Application\Auth\DTOs\LoginWithPinInput;
use App\Application\Auth\UseCases\ChangeOwnPasswordUseCase;
use App\Application\Auth\UseCases\ChangeOwnPinUseCase;
use App\Application\Auth\UseCases\GetAuthenticatedUserUseCase;
use App\Application\Auth\UseCases\ListLoginContextBranchesUseCase;
use App\Application\Auth\UseCases\ListLoginContextTenantsUseCase;
use App\Application\Auth\UseCases\LoginWithPasswordUseCase;
use App\Application\Auth\UseCases\LoginWithPinUseCase;
use App\Application\Auth\UseCases\LogoutUseCase;
use App\Application\Auth\UseCases\RefreshTokenUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangeOwnPasswordRequest;
use App\Http\Requests\Api\V1\ChangeOwnPinRequest;
use App\Http\Requests\Api\V1\LoginContextBranchesRequest;
use App\Http\Requests\Api\V1\LoginPasswordRequest;
use App\Http\Requests\Api\V1\LoginPinRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly LoginWithPinUseCase $loginWithPin,
        private readonly LoginWithPasswordUseCase $loginWithPassword,
        private readonly ListLoginContextTenantsUseCase $listLoginContextTenants,
        private readonly ListLoginContextBranchesUseCase $listLoginContextBranches,
        private readonly GetAuthenticatedUserUseCase $getAuthenticatedUser,
        private readonly LogoutUseCase $logoutUseCase,
        private readonly RefreshTokenUseCase $refreshTokenUseCase,
        private readonly ChangeOwnPasswordUseCase $changeOwnPassword,
        private readonly ChangeOwnPinUseCase $changeOwnPin,
    ) {
    }

    public function loginPin(LoginPinRequest $request): JsonResponse
    {
        $result = $this->loginWithPin->execute(new LoginWithPinInput(
            pin: $request->validated('pin'),
            tenantId: $request->validated('tenant_id'),
            tenantSlug: $request->validated('tenant_slug'),
            branchId: $request->validated('branch_id'),
            branchCode: $request->validated('branch_code'),
        ));

        return $this->presenter->present($result, 200);
    }

    public function loginContextTenants(): JsonResponse
    {
        return $this->presenter->present($this->listLoginContextTenants->execute());
    }

    public function loginContextBranches(LoginContextBranchesRequest $request): JsonResponse
    {
        return $this->presenter->present(
            $this->listLoginContextBranches->execute($request->validated('tenant_slug')),
        );
    }

    public function loginPassword(LoginPasswordRequest $request): JsonResponse
    {
        $result = $this->loginWithPassword->execute(new LoginWithPasswordInput(
            username: $request->validated('username'),
            password: $request->validated('password'),
            tenantId: $request->validated('tenant_id'),
            tenantSlug: $request->validated('tenant_slug'),
        ));

        return $this->presenter->present($result, 200);
    }

    public function me(Request $request): JsonResponse
    {
        $result = $this->getAuthenticatedUser->execute((object) [
            'userId' => $request->user()?->getAuthIdentifier(),
        ]);

        return $this->presenter->present($result, 200);
    }

    public function logout(): JsonResponse
    {
        $result = $this->logoutUseCase->execute();

        return $this->presenter->present($result, 200);
    }

    public function refresh(): JsonResponse
    {
        $result = $this->refreshTokenUseCase->execute();

        return $this->presenter->present($result, 200);
    }

    public function changePassword(ChangeOwnPasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->changeOwnPassword->execute(new ChangeOwnPasswordInput(
            userId: (int) $user->getAuthIdentifier(),
            tenantId: $user->tenant_id !== null ? (int) $user->tenant_id : null,
            branchId: $user->branch_id !== null ? (int) $user->branch_id : null,
            currentPassword: $request->validated('current_password'),
            newPassword: $request->validated('new_password'),
        ));

        return $this->presenter->present($result, 200);
    }

    public function changePin(ChangeOwnPinRequest $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->changeOwnPin->execute(new ChangeOwnPinInput(
            userId: (int) $user->getAuthIdentifier(),
            tenantId: $user->tenant_id !== null ? (int) $user->tenant_id : null,
            branchId: $user->branch_id !== null ? (int) $user->branch_id : null,
            currentPassword: $request->validated('current_password'),
            newPin: $request->validated('new_pin'),
        ));

        return $this->presenter->present($result, 200);
    }
}
