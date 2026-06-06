<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Middleware;

use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Infrastructure\Laravel\Http\Context\RequestOperationalContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRolePermissionMiddleware
{
    public function __construct(
        private readonly RequestOperationalContext $context,
    ) {
    }

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $this->context->hasPermission($permission)) {
            throw PermissionDeniedException::forPermission($permission);
        }

        return $next($request);
    }
}
