<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * @return list<array{name: string, slug: string}>
     */
    private function roleManagementPermissions(): array
    {
        return [
            ['name' => 'Acceso a roles y permisos', 'slug' => 'roles.access'],
            ['name' => 'Crear roles locales', 'slug' => 'roles.create'],
            ['name' => 'Editar roles locales', 'slug' => 'roles.update'],
            ['name' => 'Eliminar roles locales', 'slug' => 'roles.delete'],
            ['name' => 'Actualizar permisos de roles', 'slug' => 'roles.permissions.update'],
            ['name' => 'Ver catálogo de permisos', 'slug' => 'permissions.access'],
        ];
    }

    public function up(): void
    {
        $permissionIds = [];

        foreach ($this->roleManagementPermissions() as $row) {
            $permission = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );
            $permissionIds[] = $permission->id;
        }

        RoleModel::query()
            ->where(function ($query): void {
                $query->where('slug', 'super_admin')
                    ->orWhere('slug', 'tenant_owner');
            })
            ->each(function (RoleModel $role) use ($permissionIds): void {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            });
    }

    public function down(): void
    {
        $slugs = array_column($this->roleManagementPermissions(), 'slug');
        $permissions = PermissionModel::query()->whereIn('slug', $slugs)->get();

        if ($permissions->isEmpty()) {
            return;
        }

        RoleModel::query()
            ->whereIn('slug', ['super_admin', 'tenant_owner'])
            ->each(function (RoleModel $role) use ($permissions): void {
                $role->permissions()->detach($permissions->pluck('id')->all());
            });

        PermissionModel::query()->whereIn('slug', $slugs)->delete();
    }
};
