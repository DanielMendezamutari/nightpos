<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_time_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('shift_turn_id')->constrained('shift_turns')->cascadeOnDelete();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('companion_id')->nullable()->constrained('companions')->nullOnDelete();
            $table->string('customer_name', 120)->nullable();
            $table->string('room_label', 60)->nullable();
            $table->unsignedInteger('rate_per_hour');
            $table->unsignedInteger('grace_minutes')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('manual_minutes')->nullable();
            $table->unsignedInteger('billed_minutes')->default(0);
            $table->unsignedInteger('subtotal')->default(0);
            $table->enum('status', ['open', 'closed', 'paid'])->default('open');
            $table->string('notes', 400)->nullable();
            $table->timestamps();
            $table->index(['site_id', 'status']);
            $table->index(['shift_turn_id', 'status']);
        });

        Schema::create('room_time_service_extensions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_time_service_id')->constrained('room_time_services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('added_minutes');
            $table->string('notes', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('room_time_service_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_time_service_id')->constrained('room_time_services')->cascadeOnDelete();
            $table->foreignId('shift_turn_id')->constrained('shift_turns')->cascadeOnDelete();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('method', ['cash', 'qr', 'card']);
            $table->unsignedInteger('amount');
            $table->timestamp('paid_at');
            $table->timestamps();
            $table->index(['shift_turn_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_time_service_payments');
        Schema::dropIfExists('room_time_service_extensions');
        Schema::dropIfExists('room_time_services');
    }
};

