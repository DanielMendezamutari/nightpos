<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

final class ApiJwtExceptionRenderer
{
    public static function register(callable $register): void
    {
        $register(function (TokenExpiredException $exception, Request $request) {
            return self::render($request, 'Token expirado.', 'token_expired');
        });

        $register(function (TokenInvalidException $exception, Request $request) {
            return self::render($request, 'Token inválido.', 'token_invalid');
        });

        $register(function (TokenBlacklistedException $exception, Request $request) {
            Log::info('auth.token.blacklisted', ['path' => $request->path()]);

            return self::render($request, 'Token invalidado. Inicie sesión nuevamente.', 'token_blacklisted');
        });

        $register(function (JWTException $exception, Request $request) {
            if ($exception instanceof TokenExpiredException
                || $exception instanceof TokenInvalidException
                || $exception instanceof TokenBlacklistedException) {
                return null;
            }

            Log::warning('auth.jwt.error', [
                'path' => $request->path(),
                'reason' => class_basename($exception),
            ]);

            return self::render($request, 'Error de autenticación.', 'jwt_error');
        });

        $register(function (AuthenticationException $exception, Request $request) {
            return self::render($request, 'No autenticado.', 'unauthenticated');
        });
    }

    private static function render(Request $request, string $message, string $code)
    {
        if (! $request->is('api/*')) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => (object) ['code' => $code],
            'errors' => (object) [],
        ], 401);
    }
}
