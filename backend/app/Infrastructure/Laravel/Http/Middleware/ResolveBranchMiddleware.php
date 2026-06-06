<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Middleware;

use App\Domain\Auth\Exceptions\BranchAccessDeniedException;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestOperationalContext;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveBranchMiddleware
{
    public function __construct(
        private readonly RequestOperationalContext $context,
        private readonly BranchRepositoryInterface $branches,
    ) {
    }

    public function handle(Request $request, Closure $next, string $require = 'optional'): Response
    {
        $tenant = $this->context->tenant();

        if ($tenant === null) {
            return $next($request);
        }

        /** @var UserModel $user */
        $user = $request->user();
        $branch = $this->resolveBranch($request, $user, $tenant->id);

        if ($branch === null) {
            if ($require === 'required') {
                throw BranchAccessDeniedException::required();
            }

            return $next($request);
        }

        if ($branch->tenantId !== $tenant->id) {
            throw BranchAccessDeniedException::create();
        }

        if (! $branch->isActive()) {
            throw BranchAccessDeniedException::create();
        }

        $this->context->setBranch($branch);

        return $next($request);
    }

    private function resolveBranch(Request $request, UserModel $user, int $tenantId): ?\App\Domain\Branch\Entities\Branch
    {
        if ($request->filled('branch_id')) {
            return $this->branches->findById((int) $request->input('branch_id'));
        }

        if ($request->filled('branch_code')) {
            return $this->branches->findByTenantAndCode($tenantId, (string) $request->input('branch_code'));
        }

        $headerId = $request->header('X-Branch-Id');
        if ($headerId !== null && $headerId !== '') {
            return $this->branches->findById((int) $headerId);
        }

        $headerCode = $request->header('X-Branch-Code');
        if ($headerCode !== null && $headerCode !== '') {
            return $this->branches->findByTenantAndCode($tenantId, $headerCode);
        }

        if ($user->branch_id !== null) {
            return $this->branches->findById((int) $user->branch_id);
        }

        return null;
    }
}
