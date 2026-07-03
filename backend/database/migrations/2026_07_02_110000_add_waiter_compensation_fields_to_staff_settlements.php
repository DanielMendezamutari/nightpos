<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('staff_settlements', 'compensation_mode')) {
                $table->string('compensation_mode', 30)->nullable()->after('settlement_type');
            }

            if (! Schema::hasColumn('staff_settlements', 'compensation_source')) {
                $table->string('compensation_source', 40)->nullable()->after('compensation_mode');
            }

            if (! Schema::hasColumn('staff_settlements', 'manual_amount_input')) {
                $table->decimal('manual_amount_input', 12, 2)->nullable()->after('compensation_source');
            }

            if (! Schema::hasColumn('staff_settlements', 'compensation_locked_at')) {
                $table->timestamp('compensation_locked_at')->nullable()->after('manual_amount_input');
            }

            if (! Schema::hasColumn('staff_settlements', 'compensation_locked_by_user_id')) {
                $table->foreignId('compensation_locked_by_user_id')->nullable()->after('compensation_locked_at')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('staff_settlements', 'compensation_notes')) {
                $table->text('compensation_notes')->nullable()->after('compensation_locked_by_user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_settlements', function (Blueprint $table) {
            if (Schema::hasColumn('staff_settlements', 'compensation_locked_by_user_id')) {
                $table->dropConstrainedForeignId('compensation_locked_by_user_id');
            }

            $drop = [];

            foreach ([
                'compensation_mode',
                'compensation_source',
                'manual_amount_input',
                'compensation_locked_at',
                'compensation_notes',
            ] as $column) {
                if (Schema::hasColumn('staff_settlements', $column)) {
                    $drop[] = $column;
                }
            }

            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
