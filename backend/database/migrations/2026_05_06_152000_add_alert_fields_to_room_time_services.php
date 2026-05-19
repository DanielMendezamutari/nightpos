<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_time_services', function (Blueprint $table): void {
            $table->unsignedInteger('planned_minutes')->nullable()->after('grace_minutes');
            $table->unsignedInteger('alert_before_minutes')->default(5)->after('planned_minutes');
            $table->timestamp('alert_at')->nullable()->after('started_at');
            $table->timestamp('alert_notified_at')->nullable()->after('alert_at');
        });
    }

    public function down(): void
    {
        Schema::table('room_time_services', function (Blueprint $table): void {
            $table->dropColumn(['planned_minutes', 'alert_before_minutes', 'alert_at', 'alert_notified_at']);
        });
    }
};
