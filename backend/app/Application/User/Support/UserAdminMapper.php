<?php

declare(strict_types=1);

namespace App\Application\User\Support;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class UserAdminMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function user(UserModel $model): array
    {
        $branchIds = $model->accessibleBranches->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($model->branch_id !== null && ! in_array((int) $model->branch_id, $branchIds, true)) {
            $branchIds[] = (int) $model->branch_id;
        }

        $profile = $model->staffProfile;

        return [
            'id' => (int) $model->id,
            'tenant_id' => $model->tenant_id !== null ? (int) $model->tenant_id : null,
            'branch_id' => $model->branch_id !== null ? (int) $model->branch_id : null,
            'branch_name' => $model->branch?->name,
            'role_id' => $model->role_id !== null ? (int) $model->role_id : null,
            'role' => $model->role?->slug,
            'role_name' => $model->role?->name,
            'name' => $model->name,
            'username' => $model->username,
            'email' => $model->email,
            'status' => $model->status,
            'staff_role' => $profile?->staff_role,
            'waiter_commission_percent' => $profile?->waiter_commission_percent !== null
                ? (string) $profile->waiter_commission_percent
                : null,
            'can_receive_girl_commissions' => (bool) ($profile?->can_receive_girl_commissions ?? false),
            'cleaning_base_amount' => $profile?->cleaning_base_amount !== null
                ? (string) $profile->cleaning_base_amount
                : null,
            'cleaning_room_amount' => $profile?->cleaning_room_amount !== null
                ? (string) $profile->cleaning_room_amount
                : null,
            'staff_profile_status' => $profile?->status,
            'staff_notes' => $profile?->notes,
            'accessible_branch_ids' => $branchIds,
            'accessible_branches' => $model->accessibleBranches->map(static fn ($b) => [
                'id' => (int) $b->id,
                'code' => $b->code,
                'name' => $b->name,
            ])->values()->all(),
        ];
    }
}
