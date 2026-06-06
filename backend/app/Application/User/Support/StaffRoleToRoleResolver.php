<?php

declare(strict_types=1);

namespace App\Application\User\Support;

use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;

final class StaffRoleToRoleResolver
{
    /**
     * Resuelve role_id RBAC según staff_role operativo.
     */
    public static function resolveRoleId(int $tenantId, ?string $staffRole, ?int $explicitRoleId): ?int
    {
        if ($explicitRoleId !== null) {
            return $explicitRoleId;
        }

        if ($staffRole === null) {
            return null;
        }

        $slug = match ($staffRole) {
            'CASHIER' => 'cashier',
            'WAITER' => 'waiter',
            'GIRL' => 'girl',
            'CLEANING' => 'cleaning',
            'MANAGER' => 'tenant_owner',
            default => null,
        };

        if ($slug === null) {
            return null;
        }

        $role = RoleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();

        return $role?->id !== null ? (int) $role->id : null;
    }
}
