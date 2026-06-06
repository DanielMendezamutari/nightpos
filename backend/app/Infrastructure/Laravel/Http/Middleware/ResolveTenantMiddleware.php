<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Middleware;

use App\Application\Auth\Services\OperationalContextBootstrapper;
use App\Domain\Auth\Exceptions\TenantAccessDeniedException;
use App\Domain\Tenant\Exceptions\TenantNotAvailableException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestOperationalContext;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenantMiddleware
{
    public function __construct(
        private readonly RequestOperationalContext $context,
        private readonly OperationalContextBootstrapper $bootstrapper,
        private readonly TenantRepositoryInterface $tenants,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->reset();

        /** @var UserModel $user */
        $user = $request->user();
        $this->bootstrapper->bootstrapFromUser($user);

        $tenantId = $this->resolveTenantId($request, $user);
        $this->bootstrapper->assertUserMayUseTenant($tenantId, $user);

        if ($tenantId !== null) {
            $tenant = $this->tenants->findById($tenantId);

            if ($tenant === null || ! $tenant->isActive() || ! $tenant->hasValidSubscription()) {
                throw TenantNotAvailableException::inactiveOrExpired();
            }

            $this->context->setTenant($tenant);
        } elseif (! $user->isSuperAdmin()) {
            throw TenantAccessDeniedException::create();
        }

        return $next($request);
    }

    private function resolveTenantId(Request $request, UserModel $user): ?int
    {
        if (! $user->isSuperAdmin()) {
            return $user->tenant_id !== null ? (int) $user->tenant_id : null;
        }

        if ($request->filled('tenant_id')) {
            return (int) $request->input('tenant_id');
        }

        if ($request->filled('tenant_slug')) {
            $tenant = $this->tenants->findBySlug((string) $request->input('tenant_slug'));

            return $tenant?->id;
        }

        $headerId = $request->header('X-Tenant-Id');
        if ($headerId !== null && $headerId !== '') {
            return (int) $headerId;
        }

        $headerSlug = $request->header('X-Tenant-Slug');
        if ($headerSlug !== null && $headerSlug !== '') {
            $tenant = $this->tenants->findBySlug($headerSlug);

            return $tenant?->id;
        }

        if ($user->tenant_id !== null) {
            return (int) $user->tenant_id;
        }

        return null;
    }
}
