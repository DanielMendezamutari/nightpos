<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movement_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->string('name', 100);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['tenant_id', 'type', 'name']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('type', 20);
            $table->boolean('enabled')->default(true);
            $table->boolean('requires_reference')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('area_type', 20)->default('TABLE');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['branch_id', 'code']);
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->unsignedSmallInteger('default_duration_minutes')->default(60);
            $table->decimal('suggested_price', 12, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->foreignId('cash_movement_reason_id')
                ->nullable()
                ->after('movement_type')
                ->constrained('cash_movement_reasons')
                ->nullOnDelete();
            $table->text('notes')->nullable()->after('description');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('service_area_id')
                ->nullable()
                ->after('table_label')
                ->constrained('service_areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_area_id');
        });

        Schema::table('cash_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_movement_reason_id');
            $table->dropColumn('notes');
        });

        Schema::dropIfExists('room_types');
        Schema::dropIfExists('service_areas');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('cash_movement_reasons');
    }
};
