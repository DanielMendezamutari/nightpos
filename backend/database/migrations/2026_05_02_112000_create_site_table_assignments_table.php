<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_table_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('site_table_id')->constrained('site_tables')->cascadeOnDelete();
            $table->foreignId('waiter_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('site_table_id');
            $table->index(['site_id', 'waiter_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_table_assignments');
    }
};
