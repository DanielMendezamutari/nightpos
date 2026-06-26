<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Exceptions\JwtNotConfiguredException;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class JwtAuthRepository implements AuthRepositoryInterface
{
    public function issueTokenForUserId(int $userId): string
    {
        self::assertJwtConfigured();

        $user = UserModel::query()->findOrFail($userId);

        try {
            auth('api')->logout();
        } catch (\Throwable) {
            // Sin token activo en el guard.
        }

        $token = (string) auth('api')->login($user);

        auth('api')->forgetUser();
        JWTAuth::unsetToken();

        return $token;
    }

    public function refreshCurrentToken(): string
    {
        self::assertJwtConfigured();

        return (string) JWTAuth::parseToken()->refresh();
    }

    public function invalidateCurrentToken(): void
    {
        if (! config('jwt.blacklist_enabled')) {
            return;
        }

        $token = JWTAuth::getToken();

        if ($token !== null) {
            JWTAuth::invalidate($token);
        }
    }

    private static function assertJwtConfigured(): void
    {
        $secret = config('jwt.secret');
        $hasSecret = is_string($secret) && trim($secret) !== '';

        if ($hasSecret) {
            return;
        }

        $publicKey = config('jwt.keys.public');
        $privateKey = config('jwt.keys.private');
        $hasAsymmetricKeys = is_string($publicKey) && trim($publicKey) !== ''
            && is_string($privateKey) && trim($privateKey) !== '';

        if (! $hasAsymmetricKeys) {
            throw new JwtNotConfiguredException();
        }
    }
}
