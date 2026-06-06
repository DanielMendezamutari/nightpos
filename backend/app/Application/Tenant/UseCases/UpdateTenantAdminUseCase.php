<?php

declare(strict_types=1);

namespace App\Application\Tenant\UseCases;

use App\Application\Tenant\DTOs\UpdateTenantInput;
use App\Application\Tenant\Support\TenantAdminMapper;
use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Domain\Tenant\Exceptions\TenantDomainException;
use App\Domain\Tenant\Exceptions\TenantNotFoundException;
use App\Domain\Tenant\Repositories\TenantRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdateTenantAdminUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantRepositoryInterface $tenants,
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof UpdateTenantInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        if (! $this->staffContext->isSuperAdmin()) {
            throw PermissionDeniedException::forPermission('admin.tenants.update');
        }

        $existing = $this->tenants->findById($input->tenantId);

        if ($existing === null) {
            throw new TenantNotFoundException();
        }

        $name = trim($input->name);
        $slug = strtolower(trim($input->slug));

        if ($name === '') {
            throw TenantDomainException::emptyName();
        }

        if ($this->tenants->slugExists($slug, $input->tenantId)) {
            throw TenantDomainException::duplicateSlug();
        }

        if (
            $input->subscriptionStartsAt !== null
            && $input->subscriptionEndsAt !== null
            && $input->subscriptionEndsAt < $input->subscriptionStartsAt
        ) {
            throw TenantDomainException::invalidSubscriptionRange();
        }

        $tenant = $this->tenants->update(
            id: $input->tenantId,
            name: $name,
            slug: $slug,
            status: $input->status,
            planName: $input->planName,
            subscriptionStartsAt: $input->subscriptionStartsAt,
            subscriptionEndsAt: $input->subscriptionEndsAt,
        );

        return OperationResult::ok('Empresa actualizada correctamente.', [
            'tenant' => TenantAdminMapper::tenant($tenant),
        ]);
    }
}
