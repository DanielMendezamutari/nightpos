<?php

declare(strict_types=1);

use App\Application\Tenant\Support\TenantDefaultRolePermissions;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->makeAuditLogsTenantIdNullable();
        $this->repairTenantRoles();
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropForeign(['tenant_id']);
            });

            DB::statement('ALTER TABLE audit_logs MODIFY tenant_id BIGINT UNSIGNED NOT NULL');

            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    private function makeAuditLogsTenantIdNullable(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->dropForeign(['tenant_id']);
            });

            DB::statement('ALTER TABLE audit_logs MODIFY tenant_id BIGINT UNSIGNED NULL');

            Schema::table('audit_logs', function (Blueprint $table): void {
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });

            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('audit_logs_new', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 80);
                $table->string('subject_type', 80)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('metadata')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['tenant_id', 'branch_id', 'created_at']);
                $table->index(['action', 'created_at']);
            });

            DB::statement('INSERT INTO audit_logs_new SELECT * FROM audit_logs');
            Schema::drop('audit_logs');
            Schema::rename('audit_logs_new', 'audit_logs');

            Schema::enableForeignKeyConstraints();
        }
    }

    private function repairTenantRoles(): void
    {
        $roleMap = [
            'tenant_owner' => TenantDefaultRolePermissions::tenantOwner(),
            'cashier' => TenantDefaultRolePermissions::cashier(),
            'cashier_senior' => TenantDefaultRolePermissions::cashierSenior(),
            'waiter' => TenantDefaultRolePermissions::waiter(),
            'cleaning' => TenantDefaultRolePermissions::cleaning(),
            'girl' => TenantDefaultRolePermissions::girl(),
        ];

        $roleNames = [
            'tenant_owner' => 'Administrador',
            'cashier' => 'Cajero',
            'cashier_senior' => 'Cajera Senior',
            'waiter' => 'Garzón',
            'cleaning' => 'Limpieza',
            'girl' => 'Chica',
        ];

        TenantModel::query()->each(function (TenantModel $tenant) use ($roleMap, $roleNames): void {
            foreach ($roleMap as $slug => $permissionSlugs) {
                $role = RoleModel::query()->firstOrCreate(
                    ['tenant_id' => $tenant->id, 'slug' => $slug],
                    ['name' => $roleNames[$slug]],
                );

                $permissionIds = PermissionModel::query()
                    ->whereIn('slug', $permissionSlugs)
                    ->pluck('id')
                    ->all();

                if ($permissionIds !== []) {
                    $role->permissions()->syncWithoutDetaching($permissionIds);
                }
            }
        });
    }
};
