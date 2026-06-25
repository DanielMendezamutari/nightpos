<?php



use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;

use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;

use Illuminate\Database\Migrations\Migration;



return new class extends Migration

{

    public function up(): void

    {

        $permission = PermissionModel::query()->firstOrCreate(

            ['slug' => 'settlements.fines.manage'],

            ['name' => 'Gestionar multas de liquidación'],

        );



        $roleSlugs = ['tenant_owner', 'cashier_senior'];



        $roles = RoleModel::query()->whereIn('slug', $roleSlugs)->get();



        foreach ($roles as $role) {

            $role->permissions()->syncWithoutDetaching([$permission->id]);

        }

    }



    public function down(): void

    {

        $permission = PermissionModel::query()->where('slug', 'settlements.fines.manage')->first();



        if ($permission === null) {

            return;

        }



        RoleModel::query()->each(function (RoleModel $role) use ($permission): void {

            $role->permissions()->detach($permission->id);

        });



        $permission->delete();

    }

};


