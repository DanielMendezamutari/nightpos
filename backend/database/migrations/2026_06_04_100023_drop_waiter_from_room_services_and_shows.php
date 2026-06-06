<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropForeign(['waiter_user_id']);
            $table->dropColumn('waiter_user_id');
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->dropForeign(['waiter_user_id']);
            $table->dropColumn('waiter_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->foreignId('waiter_user_id')->nullable()->after('girl_user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('shows', function (Blueprint $table) {
            $table->foreignId('waiter_user_id')->nullable()->after('girl_user_id')->constrained('users')->nullOnDelete();
        });
    }
};
