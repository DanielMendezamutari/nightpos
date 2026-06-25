<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('staff_settlements', 'payment_method')) {
                $table->string('payment_method', 20)->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('staff_settlements', 'cash_movement_id')) {
                $table->foreignId('cash_movement_id')->nullable()->after('payment_method')
                    ->constrained('cash_movements')->nullOnDelete();
            }
            if (! Schema::hasColumn('staff_settlements', 'ticket_number')) {
                $table->string('ticket_number', 40)->nullable()->after('cash_movement_id');
            }
            if (! Schema::hasColumn('staff_settlements', 'print_count')) {
                $table->unsignedInteger('print_count')->default(0)->after('ticket_number');
            }
            if (! Schema::hasColumn('staff_settlements', 'last_printed_at')) {
                $table->timestamp('last_printed_at')->nullable()->after('print_count');
            }
            if (! Schema::hasColumn('staff_settlements', 'last_printed_by_user_id')) {
                $table->foreignId('last_printed_by_user_id')->nullable()->after('last_printed_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('staff_settlements', 'print_job_id')) {
                $table->foreignId('print_job_id')->nullable()->after('last_printed_by_user_id')
                    ->constrained('print_jobs')->nullOnDelete();
            }
            if (! Schema::hasColumn('staff_settlements', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('print_job_id');
            }
        });

        Schema::table('staff_settlements', function (Blueprint $table) {
            if (Schema::hasColumn('staff_settlements', 'ticket_number')) {
                $table->unique('ticket_number', 'staff_settlements_ticket_number_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            $table->dropUnique('staff_settlements_ticket_number_unique');
            $table->dropConstrainedForeignId('print_job_id');
            $table->dropConstrainedForeignId('last_printed_by_user_id');
            $table->dropConstrainedForeignId('cash_movement_id');
            $table->dropColumn([
                'payment_method',
                'ticket_number',
                'print_count',
                'last_printed_at',
                'version',
            ]);
        });
    }
};
