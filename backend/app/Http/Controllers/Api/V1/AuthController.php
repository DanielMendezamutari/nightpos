<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Auth\Commands\LoginWithPasswordCommand;
use App\Application\Auth\Commands\LoginWithPinCommand;
use App\Application\Auth\Handlers\LoginWithPasswordHandler;
use App\Application\Auth\Handlers\LoginWithPinHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Throwable;

final class AuthController extends Controller
{
    public function __construct(
        private LoginWithPinHandler $loginWithPinHandler,
        private LoginWithPasswordHandler $loginWithPasswordHandler
    ) {}

    public function loginPin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string'],
            'tenant_slug' => ['required', 'string'],
            'branch_code' => ['nullable', 'string'],
        ]);

        try {
            $result = $this->loginWithPinHandler->handle(new LoginWithPinCommand(
                pin: $validated['pin'],
                tenantSlug: $validated['tenant_slug'],
                branchCode: $validated['branch_code'] ?? null
            ));

            return response()->json([
                'success' => true,
                'message' => 'Autenticacion exitosa',
                'data' => $result->toArray(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function loginPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'tenant_slug' => ['required', 'string'],
        ]);

        try {
            $result = $this->loginWithPasswordHandler->handle(new LoginWithPasswordCommand(
                username: $validated['username'],
                password: $validated['password'],
                tenantSlug: $validated['tenant_slug']
            ));

            return response()->json([
                'success' => true,
                'message' => 'Autenticacion administrativa exitosa',
                'data' => $result->toArray(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'tenant_id' => $user->tenant_id,
                    'branch_id' => $user->branch_id,
                ],
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success' => true, 'message' => 'Sesion cerrada exitosamente']);
        } catch (Throwable $e) {
            return response()->json(['success' => true, 'message' => 'Sesion finalizada']);
        }
    }
}
