<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_drawer_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shift_turn_id')->constrained('shift_turns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->unsignedInteger('amount');
            $table->string('notes', 400)->nullable();
            $table->timestamps();
            $table->index(['shift_turn_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_drawer_movements');
    }
};
