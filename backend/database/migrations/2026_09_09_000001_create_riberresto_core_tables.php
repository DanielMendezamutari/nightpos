<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_brand')->default('Ribersoft');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code')->index();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('nit')->nullable();
            $table->string('municipality')->default('Santa Cruz');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id')->nullable();
            $table->string('name');
            $table->string('username')->index();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('pin_hash')->nullable();
            $table->string('role')->default('mesero');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unique(['tenant_id', 'username']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('tenants');
    }
};
