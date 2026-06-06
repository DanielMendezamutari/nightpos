<?php

declare(strict_types=1);

namespace App\Application\Branch\Support;

use App\Domain\Branch\Entities\Branch;

final class BranchAdminMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function branch(Branch $branch): array
    {
        return [
            'id' => $branch->id,
            'tenant_id' => $branch->tenantId,
            'name' => $branch->name,
            'code' => $branch->code,
            'address' => $branch->address,
            'status' => $branch->status,
        ];
    }
}
