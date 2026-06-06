<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Auth\DTOs\LoginWithPasswordInput;
use App\Application\Auth\DTOs\LoginWithPinInput;
use App\Application\Auth\UseCases\GetAuthenticatedUserUseCase;
use App\Application\Auth\UseCases\LoginWithPasswordUseCase;
use App\Application\Auth\UseCases\LoginWithPinUseCase;
use App\Application\Auth\UseCases\LogoutUseCase;
use App\Http\Controllers\Controller;
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
        private readonly GetAuthenticatedUserUseCase $getAuthenticatedUser,
        private readonly LogoutUseCase $logoutUseCase,
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
}
