<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use App\Domain\Branch\Entities\Branch;
use App\Shared\Domain\ValueObjects\BranchId;

interface BranchContextInterface
{
    public function branchId(): ?BranchId;

    public function branch(): ?Branch;

    public function hasBranch(): bool;
}
