<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bracelets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
            $table->foreignId('girl_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('registered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('registered_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'official_shift_id']);
            $table->index(['girl_user_id', 'official_shift_id']);
        });

        Schema::create('room_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
            $table->foreignId('girl_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('room_number', 30)->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('registered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('registered_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'official_shift_id']);
            $table->index(['girl_user_id', 'official_shift_id']);
        });

        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
            $table->foreignId('girl_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('show_type', 50);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->foreignId('registered_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('registered_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'official_shift_id']);
            $table->index(['girl_user_id', 'official_shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shows');
        Schema::dropIfExists('room_services');
        Schema::dropIfExists('bracelets');
    }
};
