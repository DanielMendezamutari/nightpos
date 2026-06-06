<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Context;

use App\Domain\Tenant\Entities\Tenant;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Domain\ValueObjects\BranchId;
use App\Shared\Domain\ValueObjects\TenantId;

final class TenantContext implements TenantContextInterface
{
    public function __construct(
        private readonly RequestOperationalContext $context,
    ) {
    }

    public function tenantId(): ?TenantId
    {
        $tenant = $this->context->tenant();

        return $tenant !== null ? new TenantId((string) $tenant->id) : null;
    }

    public function tenant(): ?Tenant
    {
        return $this->context->tenant();
    }

    public function branchId(): ?BranchId
    {
        $branch = $this->context->branch();

        return $branch !== null ? new BranchId((string) $branch->id) : null;
    }

    public function hasTenant(): bool
    {
        return $this->context->tenant() !== null;
    }

    public function hasBranch(): bool
    {
        return $this->context->branch() !== null;
    }
}
