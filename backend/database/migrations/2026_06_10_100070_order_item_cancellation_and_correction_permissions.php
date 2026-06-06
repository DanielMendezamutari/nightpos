<?php

use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('notes');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->foreignId('cancelled_by_user_id')->nullable()->after('cancelled_at')
                ->constrained('users')->nullOnDelete();
        });

        $definitions = [
            ['name' => 'Editar ítems comanda', 'slug' => 'orders.update_items'],
            ['name' => 'Cancelar línea comanda', 'slug' => 'orders.cancel_item'],
            ['name' => 'Editar cabecera comanda', 'slug' => 'orders.update_header'],
            ['name' => 'Cancelar comanda', 'slug' => 'orders.cancel'],
        ];

        $permissionIds = [];

        foreach ($definitions as $row) {
            $permission = PermissionModel::query()->firstOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name']],
            );
            $permissionIds[] = $permission->id;
        }

        $slugs = [
            'tenant_owner',
            'cashier',
            'cashier_senior',
        ];

        foreach ($slugs as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();

            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by_user_id');
            $table->dropColumn(['cancellation_reason', 'cancelled_at']);
        });

        $slugs = ['orders.update_items', 'orders.cancel_item', 'orders.update_header', 'orders.cancel'];
        $ids = PermissionModel::query()->whereIn('slug', $slugs)->pluck('id');
        RoleModel::query()->each(function (RoleModel $role) use ($ids) {
            $role->permissions()->detach($ids);
        });
        PermissionModel::query()->whereIn('slug', $slugs)->delete();
    }
};
