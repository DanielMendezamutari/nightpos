<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class JwtAuthRepository implements AuthRepositoryInterface
{
    public function issueTokenForUserId(int $userId): string
    {
        $user = UserModel::query()->findOrFail($userId);

        auth('api')->logout();

        return (string) auth('api')->login($user);
    }

    public function invalidateCurrentToken(): void
    {
        $token = JWTAuth::getToken();

        if ($token !== null) {
            JWTAuth::invalidate($token);
        }
    }
}
