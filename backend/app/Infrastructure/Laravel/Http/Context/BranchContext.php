<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Context;

use App\Domain\Branch\Entities\Branch;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Domain\ValueObjects\BranchId;

final class BranchContext implements BranchContextInterface
{
    public function __construct(
        private readonly RequestOperationalContext $context,
    ) {
    }

    public function branchId(): ?BranchId
    {
        $branch = $this->context->branch();

        return $branch !== null ? new BranchId((string) $branch->id) : null;
    }

    public function branch(): ?Branch
    {
        return $this->context->branch();
    }

    public function hasBranch(): bool
    {
        return $this->context->branch() !== null;
    }
}
