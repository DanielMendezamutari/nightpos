<?php

declare(strict_types=1);

namespace App\Application\Product\Support;

use App\Shared\Contracts\BranchContextInterface;

final class BranchScopeResolver
{
    public static function resolve(?int $explicit, BranchContextInterface $branchContext): ?int
    {
        if ($explicit !== null) {
            return $explicit;
        }

        $branchId = $branchContext->branchId();

        return $branchId !== null ? (int) $branchId->value : null;
    }
}
