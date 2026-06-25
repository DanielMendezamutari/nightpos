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
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->boolean('is_forced_close')->default(false)->after('closing_notes');
            $table->foreignId('forced_closed_by_user_id')->nullable()->after('is_forced_close')->constrained('users')->nullOnDelete();
            $table->timestamp('forced_closed_at')->nullable()->after('forced_closed_by_user_id');
            $table->string('forced_close_reason', 50)->nullable()->after('forced_closed_at');
            $table->text('forced_close_notes')->nullable()->after('forced_close_reason');
            $table->json('close_blockers_snapshot')->nullable()->after('forced_close_notes');
            $table->json('financial_summary_snapshot')->nullable()->after('close_blockers_snapshot');
        });

        $permission = PermissionModel::query()->firstOrCreate(
            ['slug' => 'admin.cash_sessions.force_close'],
            ['name' => 'Cierre administrativo de caja'],
        );

        foreach (['super_admin', 'tenant_owner', 'cashier_senior'] as $slug) {
            $role = RoleModel::query()->where('slug', $slug)->first();
            if ($role !== null) {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('cash_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forced_closed_by_user_id');
            $table->dropColumn([
                'is_forced_close',
                'forced_closed_at',
                'forced_close_reason',
                'forced_close_notes',
                'close_blockers_snapshot',
                'financial_summary_snapshot',
            ]);
        });
    }
};
