<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companion_work_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('shift_turn_id')->constrained('shift_turns')->cascadeOnDelete();
            $table->foreignId('companion_id')->constrained('companions')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['active', 'settled'])->default('active');
            $table->timestamps();
            $table->index(['shift_turn_id', 'companion_id', 'status']);
        });

        Schema::create('companion_work_session_payouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('companion_work_session_id')
                ->constrained('companion_work_sessions')
                ->cascadeOnDelete();
            $table->foreignId('cashier_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->timestamp('paid_at');
            $table->string('notes', 400)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companion_work_session_payouts');
        Schema::dropIfExists('companion_work_sessions');
    }
};
