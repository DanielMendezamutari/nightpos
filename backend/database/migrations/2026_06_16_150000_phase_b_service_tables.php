<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('service_area_id')->constrained('service_areas')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('label', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
            $table->index(['branch_id', 'service_area_id', 'status']);
        });

        Schema::create('waiter_table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_table_id')->constrained('service_tables')->cascadeOnDelete();
            $table->foreignId('official_shift_id')->nullable()->constrained('official_shifts')->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->unique(['branch_id', 'waiter_user_id', 'service_table_id', 'official_shift_id'], 'waiter_table_shift_unique');
            $table->index(['branch_id', 'waiter_user_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('service_table_id')
                ->nullable()
                ->after('service_area_id')
                ->constrained('service_tables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_table_id');
        });

        Schema::dropIfExists('waiter_table_assignments');
        Schema::dropIfExists('service_tables');
    }
};
