<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['staff.quick_create_girl', 'settings.service_areas'] as $slug) {
            $permission = PermissionModel::query()->where('slug', $slug)->first();

            if ($permission === null) {
                continue;
            }

            foreach (RoleModel::query()->where('slug', 'waiter')->get() as $role) {
                $role->permissions()->syncWithoutDetaching([(int) $permission->id]);
            }
        }
    }

    public function down(): void
    {
        $slugs = ['staff.quick_create_girl', 'settings.service_areas'];

        foreach ($slugs as $slug) {
            $permission = PermissionModel::query()->where('slug', $slug)->first();

            if ($permission === null) {
                continue;
            }

            foreach (RoleModel::query()->where('slug', 'waiter')->get() as $role) {
                $role->permissions()->detach((int) $permission->id);
            }
        }
    }
};
