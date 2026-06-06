<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            ['name' => 'Ver manillas', 'slug' => 'bracelets.access'],
            ['name' => 'Registrar manillas', 'slug' => 'bracelets.create'],
            ['name' => 'Ver piezas', 'slug' => 'room_services.access'],
            ['name' => 'Registrar piezas', 'slug' => 'room_services.create'],
            ['name' => 'Ver shows', 'slug' => 'shows.access'],
            ['name' => 'Registrar shows', 'slug' => 'shows.create'],
        ])->map(fn (array $row) => PermissionModel::query()->firstOrCreate(
            ['slug' => $row['slug']],
            ['name' => $row['name']],
        ));

        $all = $permissions->pluck('id');

        foreach (['super_admin', 'tenant_owner'] as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($all);
            }
        }

        $cashier = RoleModel::query()->where('slug', 'cashier')->first();
        if ($cashier !== null) {
            $cashier->permissions()->syncWithoutDetaching($all);
        }
    }

    public function down(): void
    {
        // Permissions retained for data safety.
    }
};
