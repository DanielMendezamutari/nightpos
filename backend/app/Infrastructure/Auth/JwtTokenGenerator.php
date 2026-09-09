<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Ports\TokenGeneratorInterface;
use App\Domain\User\Entities\User;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class JwtTokenGenerator implements TokenGeneratorInterface
{
    public function generateForUser(User $user): string
    {
        $eloquentUser = UserModel::query()->findOrFail($user->id());
        return JWTAuth::fromUser($eloquentUser);
    }
}
