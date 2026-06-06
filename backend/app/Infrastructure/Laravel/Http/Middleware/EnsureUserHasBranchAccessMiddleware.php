<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Middleware;

use App\Application\Auth\Services\BranchAccessGuard;
use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Infrastructure\Laravel\Http\Context\RequestOperationalContext;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserHasBranchAccessMiddleware
{
    public function __construct(
        private readonly RequestOperationalContext $context,
        private readonly BranchAccessGuard $branchAccessGuard,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $branch = $this->context->branch();
        $tenant = $this->context->tenant();

        if ($branch === null || $tenant === null) {
            return $next($request);
        }

        /** @var UserModel $user */
        $user = $request->user();

        if (! $user->isActive()) {
            throw BranchAccessDeniedException::create();
        }

        $this->branchAccessGuard->assertUserCanAccessBranch($user, $branch->id, $tenant->id);

        return $next($request);
    }
}
