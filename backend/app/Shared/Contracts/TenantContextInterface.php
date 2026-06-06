<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use App\Domain\Tenant\Entities\Tenant;
use App\Shared\Domain\ValueObjects\BranchId;
use App\Shared\Domain\ValueObjects\TenantId;

interface TenantContextInterface
{
    public function tenantId(): ?TenantId;

    public function tenant(): ?Tenant;

    public function branchId(): ?BranchId;

    public function hasTenant(): bool;

    public function hasBranch(): bool;
}
