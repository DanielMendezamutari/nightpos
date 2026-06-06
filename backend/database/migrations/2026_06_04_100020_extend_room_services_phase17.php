<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->string('room_label', 50)->nullable()->after('room_number');
            $table->dateTime('started_at')->nullable()->after('registered_at');
            $table->unsignedInteger('duration_minutes')->default(60)->after('started_at');
            $table->dateTime('expected_ends_at')->nullable()->after('duration_minutes');
            $table->dateTime('ended_at')->nullable()->after('expected_ends_at');
            $table->string('status', 20)->default('ACTIVE')->after('ended_at');
            $table->foreignId('cleaning_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by_user_id')->nullable()->after('cleaning_user_id')->constrained('users')->nullOnDelete();
            $table->dateTime('checked_at')->nullable()->after('checked_by_user_id');
            $table->dateTime('alert_sent_at')->nullable()->after('checked_at');

            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['status', 'expected_ends_at']);
        });

        foreach (DB::table('room_services')->orderBy('id')->get() as $row) {
            $started = $row->registered_at;
            $duration = 60;
            $expected = date('Y-m-d H:i:s', strtotime($started.' +'.$duration.' minutes'));

            DB::table('room_services')->where('id', $row->id)->update([
                'room_label' => $row->room_number,
                'started_at' => $started,
                'duration_minutes' => $duration,
                'expected_ends_at' => $expected,
                'status' => 'FINISHED',
                'ended_at' => $started,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'branch_id', 'status']);
            $table->dropIndex(['status', 'expected_ends_at']);
            $table->dropConstrainedForeignId('cleaning_user_id');
            $table->dropConstrainedForeignId('checked_by_user_id');
            $table->dropColumn([
                'room_label',
                'started_at',
                'duration_minutes',
                'expected_ends_at',
                'ended_at',
                'status',
                'checked_at',
                'alert_sent_at',
            ]);
        });
    }
};
