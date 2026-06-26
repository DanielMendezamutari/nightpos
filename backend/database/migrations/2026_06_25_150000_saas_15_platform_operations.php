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
        if (! Schema::hasColumn('print_devices', 'host_name')) {
            Schema::table('print_devices', function (Blueprint $table): void {
                $table->string('host_name', 120)->nullable()->after('agent_version');
                $table->string('os_name', 80)->nullable()->after('host_name');
                $table->string('os_version', 80)->nullable()->after('os_name');
                $table->string('arch', 40)->nullable()->after('os_version');
                $table->string('ip_address', 45)->nullable()->after('arch');
                $table->string('printer_model', 120)->nullable()->after('ip_address');
                $table->timestamp('last_printed_at')->nullable()->after('last_seen_at');
                $table->timestamp('installed_at')->nullable()->after('last_printed_at');
            });
        }

        if (! Schema::hasTable('tenant_technical_profiles')) {
            Schema::create('tenant_technical_profiles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('primary_pc_name', 120)->nullable();
                $table->string('operating_system', 120)->nullable();
                $table->string('ram', 40)->nullable();
                $table->string('printer_model', 120)->nullable();
                $table->string('printer_connection_type', 60)->nullable();
                $table->string('remote_support_tool', 60)->nullable();
                $table->string('remote_support_id', 120)->nullable();
                $table->string('installer_name', 120)->nullable();
                $table->timestamp('installed_at')->nullable();
                $table->text('installation_notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'branch_id'], 'tenant_tech_profile_uq');
            });
        }

        if (! Schema::hasTable('tenant_operation_checklist_items')) {
            Schema::create('tenant_operation_checklist_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('item_key', 60);
                $table->string('label', 160);
                $table->boolean('completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['tenant_id', 'branch_id', 'item_key'], 'tenant_ops_chk_uq');
            });
        }

        $permission = PermissionModel::query()->firstOrCreate(
            ['slug' => 'platform.operations.view'],
            ['name' => 'Ver operaciones plataforma (Control Center)'],
        );

        RoleModel::query()
            ->whereNull('tenant_id')
            ->where('slug', 'super_admin')
            ->each(function (RoleModel $role) use ($permission): void {
                $role->permissions()->syncWithoutDetaching([$permission->id]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_operation_checklist_items');
        Schema::dropIfExists('tenant_technical_profiles');

        if (Schema::hasColumn('print_devices', 'host_name')) {
            Schema::table('print_devices', function (Blueprint $table): void {
                $table->dropColumn([
                    'host_name',
                    'os_name',
                    'os_version',
                    'arch',
                    'ip_address',
                    'printer_model',
                    'last_printed_at',
                    'installed_at',
                ]);
            });
        }

        $permission = PermissionModel::query()->where('slug', 'platform.operations.view')->first();
        if ($permission !== null) {
            RoleModel::query()->each(function (RoleModel $role) use ($permission): void {
                $role->permissions()->detach($permission->id);
            });
            $permission->delete();
        }
    }
};
