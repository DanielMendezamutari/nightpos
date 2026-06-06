<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
        });

        $permission = PermissionModel::query()->firstOrCreate(
            ['slug' => 'staff.quick_create_girl'],
            ['name' => 'Alta rápida de chica'],
        );

        foreach (['tenant_owner', 'cashier'] as $roleSlug) {
            $role = RoleModel::query()->where('slug', $roleSlug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        PermissionModel::query()->where('slug', 'staff.quick_create_girl')->delete();
    }
};
