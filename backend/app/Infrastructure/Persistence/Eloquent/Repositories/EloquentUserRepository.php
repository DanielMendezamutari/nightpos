<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\AuthenticatedUser;
use App\Domain\User\Exceptions\UserDomainException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\PinFingerprint;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Hash;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?AuthenticatedUser
    {
        $model = UserModel::query()
            ->with(['role.permissions', 'staffProfile', 'accessibleBranches'])
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function findCandidateIdsByPinScope(?int $tenantId, ?int $branchId, ?string $branchCode): array
    {
        $query = UserModel::query()
            ->where('status', 'active')
            ->whereNotNull('pin_hash');

        if ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        $this->applyPinBranchScope($query, $tenantId, $branchId, $branchCode);

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function findUserIdByPinFingerprintInScope(
        string $pinFingerprint,
        ?int $tenantId,
        ?int $branchId,
        ?string $branchCode,
    ): ?int {
        $query = UserModel::query()
            ->where('status', 'active')
            ->where('pin_fingerprint', $pinFingerprint);

        if ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        }

        $this->applyPinBranchScope($query, $tenantId, $branchId, $branchCode);

        $id = $query->value('id');

        return $id !== null ? (int) $id : null;
    }

    public function findByUsername(?int $tenantId, string $username): ?AuthenticatedUser
    {
        $query = UserModel::query()
            ->with(['role.permissions', 'staffProfile', 'accessibleBranches'])
            ->where('username', $username)
            ->where('status', 'active');

        if ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            });
        } else {
            $query->whereNull('tenant_id');
        }

        $model = $query->first();

        return $model ? $this->map($model) : null;
    }

    public function findByUsernameForLogin(?int $tenantId, string $username): ?AuthenticatedUser
    {
        $model = UserModel::query()
            ->with(['role.permissions', 'staffProfile', 'accessibleBranches'])
            ->where('username', $username)
            ->where('status', 'active')
            ->first();

        if ($model === null) {
            return null;
        }

        if ($model->tenant_id === null) {
            return $this->map($model);
        }

        if ($tenantId === null) {
            return null;
        }

        if ((int) $model->tenant_id !== $tenantId) {
            return null;
        }

        return $this->map($model);
    }

    public function getPinHashById(int $userId): ?string
    {
        return UserModel::query()->whereKey($userId)->value('pin_hash');
    }

    public function isPinFingerprintTaken(string $pinFingerprint, ?int $exceptUserId = null): bool
    {
        $query = UserModel::query()->where('pin_fingerprint', $pinFingerprint);

        if ($exceptUserId !== null) {
            $query->where('id', '!=', $exceptUserId);
        }

        return $query->exists();
    }

    public function getPasswordHashById(int $userId): ?string
    {
        return UserModel::query()->whereKey($userId)->value('password');
    }

    public function recordLastLogin(int $userId): void
    {
        UserModel::query()->whereKey($userId)->update(['last_login_at' => now()]);
    }

    public function listByTenant(int $tenantId): array
    {
        return UserModel::query()
            ->with(['role.permissions', 'staffProfile', 'accessibleBranches'])
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (UserModel $model) => $this->map($model))
            ->all();
    }

    public function findByIdForTenant(int $userId, int $tenantId): ?AuthenticatedUser
    {
        $model = UserModel::query()
            ->with(['role.permissions', 'staffProfile', 'accessibleBranches', 'branch'])
            ->where('tenant_id', $tenantId)
            ->find($userId);

        return $model ? $this->map($model) : null;
    }

    public function activeGirlExistsByNameInBranch(int $tenantId, int $branchId, string $name): bool
    {
        return $this->activeStaffExistsByNameInBranch($tenantId, $branchId, $name, 'GIRL');
    }

    public function activeWaiterExistsByNameInBranch(int $tenantId, int $branchId, string $name): bool
    {
        return $this->activeStaffExistsByNameInBranch($tenantId, $branchId, $name, 'WAITER');
    }

    private function activeStaffExistsByNameInBranch(int $tenantId, int $branchId, string $name, string $staffRole): bool
    {
        $normalized = mb_strtolower(trim($name));

        return UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                    ->orWhereHas('accessibleBranches', fn ($b) => $b->where('branches.id', $branchId));
            })
            ->whereHas('staffProfile', fn ($profile) => $profile
                ->where('staff_role', $staffRole)
                ->where('status', 'active'))
            ->exists();
    }

    public function createForTenant(
        int $tenantId,
        ?int $branchId,
        ?int $roleId,
        string $name,
        string $username,
        ?string $email,
        ?string $password,
        ?string $pinPlain,
        string $status,
        ?string $staffRole,
        ?string $waiterCommissionPercent,
        bool $canReceiveGirlCommissions = false,
        array $accessibleBranchIds = [],
        ?string $staffNotes = null,
        ?string $cleaningBaseAmount = null,
        ?string $cleaningRoomAmount = null,
    ): AuthenticatedUser {
        $pinHash = null;
        $pinFingerprint = null;

        if ($pinPlain !== null) {
            $pinFingerprint = PinFingerprint::fromPlain($pinPlain, (string) config('app.key'));

            if ($this->isPinFingerprintTaken($pinFingerprint)) {
                throw UserDomainException::duplicatePin();
            }

            $pinHash = Hash::make($pinPlain);
        }

        $model = UserModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'role_id' => $roleId,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'pin_hash' => $pinHash,
            'pin_fingerprint' => $pinFingerprint,
            'status' => $status,
        ]);

        if ($staffRole !== null) {
            StaffProfileModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'user_id' => $model->id,
                'staff_role' => $staffRole,
                'waiter_commission_percent' => $waiterCommissionPercent,
                'can_receive_girl_commissions' => $canReceiveGirlCommissions,
                'cleaning_base_amount' => $cleaningBaseAmount,
                'cleaning_room_amount' => $cleaningRoomAmount,
                'status' => 'active',
                'notes' => $staffNotes,
            ]);
        }

        $this->syncAccessibleBranches($model, $tenantId, $accessibleBranchIds);

        return $this->map($model->fresh(['role.permissions', 'staffProfile', 'accessibleBranches', 'branch']));
    }

    public function updateForTenant(
        int $userId,
        int $tenantId,
        ?int $branchId,
        ?int $roleId,
        string $name,
        string $username,
        ?string $email,
        string $status,
        ?string $staffRole,
        ?string $waiterCommissionPercent,
        bool $canReceiveGirlCommissions,
        array $accessibleBranchIds,
        ?string $cleaningBaseAmount = null,
        ?string $cleaningRoomAmount = null,
    ): AuthenticatedUser {
        $model = $this->requireModelForTenant($userId, $tenantId);

        $model->update([
            'branch_id' => $branchId,
            'role_id' => $roleId,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'status' => $status,
        ]);

        if ($staffRole !== null) {
            StaffProfileModel::query()->updateOrCreate(
                ['user_id' => $model->id],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'staff_role' => $staffRole,
                    'waiter_commission_percent' => $waiterCommissionPercent,
                    'can_receive_girl_commissions' => $canReceiveGirlCommissions,
                    'cleaning_base_amount' => $cleaningBaseAmount,
                    'cleaning_room_amount' => $cleaningRoomAmount,
                    'status' => 'active',
                ],
            );
        }

        $this->syncAccessibleBranches($model, $tenantId, $accessibleBranchIds);

        return $this->map($model->fresh(['role.permissions', 'staffProfile', 'accessibleBranches', 'branch']));
    }

    public function resetPinForTenant(int $userId, int $tenantId, string $pinPlain): void
    {
        $model = $this->requireModelForTenant($userId, $tenantId);
        $pinFingerprint = PinFingerprint::fromPlain($pinPlain, (string) config('app.key'));

        if ($this->isPinFingerprintTaken($pinFingerprint, $userId)) {
            throw UserDomainException::duplicatePin();
        }

        $model->update([
            'pin_hash' => Hash::make($pinPlain),
            'pin_fingerprint' => $pinFingerprint,
        ]);
    }

    public function resetPasswordForTenant(int $userId, int $tenantId, string $passwordPlain): void
    {
        $model = $this->requireModelForTenant($userId, $tenantId);

        $model->update([
            'password' => $passwordPlain,
        ]);
    }

    public function grantBranchAccess(int $userId, int $tenantId, int $branchId): void
    {
        $this->requireModelForTenant($userId, $tenantId);
        $this->assertBranchInTenant($tenantId, $branchId);

        UserBranchAccessModel::query()->firstOrCreate([
            'user_id' => $userId,
            'branch_id' => $branchId,
        ], [
            'tenant_id' => $tenantId,
        ]);
    }

    public function revokeBranchAccess(int $userId, int $tenantId, int $branchId): void
    {
        $this->requireModelForTenant($userId, $tenantId);

        UserBranchAccessModel::query()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->delete();
    }

    private function requireModelForTenant(int $userId, int $tenantId): UserModel
    {
        $model = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->find($userId);

        if ($model === null) {
            throw UserDomainException::notFound();
        }

        return $model;
    }

    private function assertBranchInTenant(int $tenantId, int $branchId): void
    {
        $exists = BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->whereKey($branchId)
            ->exists();

        if (! $exists) {
            throw UserDomainException::branchNotInTenant();
        }
    }

    /**
     * @param  list<int>  $accessibleBranchIds
     */
    /**
     * Usuarios plataforma (tenant_id null) pueden autenticarse con PIN en cualquier sucursal del tenant indicado.
     */
    private function applyPinBranchScope(
        \Illuminate\Database\Eloquent\Builder $query,
        ?int $tenantId,
        ?int $branchId,
        ?string $branchCode,
    ): void {
        $resolvedBranchId = $branchId;

        if ($resolvedBranchId === null && $branchCode !== null && $tenantId !== null) {
            $resolvedBranchId = BranchModel::query()
                ->where('tenant_id', $tenantId)
                ->where('code', $branchCode)
                ->value('id');
            $resolvedBranchId = $resolvedBranchId !== null ? (int) $resolvedBranchId : null;
        }

        if ($resolvedBranchId === null) {
            return;
        }

        $query->where(function ($q) use ($resolvedBranchId) {
            $q->where('branch_id', $resolvedBranchId)
                ->orWhereHas('branchAccess', fn ($access) => $access->where('branch_id', $resolvedBranchId))
                ->orWhereNull('tenant_id');
        });
    }

    private function syncAccessibleBranches(UserModel $model, int $tenantId, array $accessibleBranchIds): void
    {
        foreach ($accessibleBranchIds as $branchId) {
            $this->assertBranchInTenant($tenantId, (int) $branchId);
            UserBranchAccessModel::query()->firstOrCreate([
                'user_id' => $model->id,
                'branch_id' => (int) $branchId,
            ], [
                'tenant_id' => $tenantId,
            ]);
        }
    }

    private function map(UserModel $model): AuthenticatedUser
    {
        $branchIds = $model->accessibleBranches->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($model->branch_id !== null && ! in_array((int) $model->branch_id, $branchIds, true)) {
            $branchIds[] = (int) $model->branch_id;
        }

        $permissions = $model->role
            ? $model->role->permissions->pluck('slug')->all()
            : [];

        return new AuthenticatedUser(
            id: (int) $model->id,
            tenantId: $model->tenant_id !== null ? (int) $model->tenant_id : null,
            branchId: $model->branch_id !== null ? (int) $model->branch_id : null,
            name: $model->name,
            username: $model->username,
            email: $model->email,
            status: $model->status,
            roleSlug: $model->role?->slug,
            staffRole: $model->staffProfile?->staff_role,
            waiterCommissionPercent: $model->staffProfile?->waiter_commission_percent !== null
                ? (string) $model->staffProfile->waiter_commission_percent
                : null,
            canReceiveGirlCommissions: (bool) ($model->staffProfile?->can_receive_girl_commissions ?? false),
            accessibleBranchIds: $branchIds,
            permissions: $permissions,
        );
    }
}
