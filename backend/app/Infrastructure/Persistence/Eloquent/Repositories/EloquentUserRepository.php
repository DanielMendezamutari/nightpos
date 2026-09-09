<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Hash;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByPin(string $tenantId, ?string $branchCode, string $plainPin): ?User
    {
        $query = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($branchCode !== null && $branchCode !== '') {
            $branch = BranchModel::query()
                ->where('tenant_id', $tenantId)
                ->where('code', $branchCode)
                ->first();

            if ($branch) {
                $query->where(function ($q) use ($branch) {
                    $q->where('branch_id', $branch->id)
                      ->orWhereNull('branch_id'); // Admins can login across branches
                });
            }
        }

        $users = $query->get();

        foreach ($users as $userModel) {
            if ($userModel->pin_hash && Hash::check($plainPin, $userModel->pin_hash)) {
                return $this->toDomain($userModel);
            }
        }

        return null;
    }

    public function findByUsername(string $tenantId, string $username): ?User
    {
        $userModel = UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('username', $username)
            ->where('is_active', true)
            ->first();

        return $userModel ? $this->toDomain($userModel) : null;
    }

    public function findById(string $id): ?User
    {
        $userModel = UserModel::query()->find($id);
        return $userModel ? $this->toDomain($userModel) : null;
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id: (string) $model->id,
            tenantId: (string) $model->tenant_id,
            branchId: $model->branch_id ? (string) $model->branch_id : null,
            name: $model->name,
            username: $model->username,
            email: $model->email,
            role: $model->role,
            isActive: (bool) $model->is_active
        );
    }
}
