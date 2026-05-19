<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    /**
     * API routes use JWT; issue a Bearer token instead of Sanctum/session.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string|null  $guard
     */
    public function actingAs($user, $guard = null): static
    {
        if ($user instanceof User) {
            Auth::forgetGuards();
            app('tymon.jwt')->unsetToken();
            app('tymon.jwt.auth')->unsetToken();

            $token = JWTAuth::fromUser($user);

            return $this->flushHeaders()->withHeaders([
                'Authorization' => 'Bearer '.$token,
            ]);
        }

        return parent::actingAs($user, $guard);
    }
}
