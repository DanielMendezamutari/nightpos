<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('staff_profiles', 'cleaning_base_amount')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->decimal('cleaning_base_amount', 12, 2)->nullable()->after('can_receive_girl_commissions');
                $table->decimal('cleaning_room_amount', 12, 2)->nullable()->after('cleaning_base_amount');
            });
        }

        if (! Schema::hasTable('cleaning_tasks')) {
            Schema::create('cleaning_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('room_service_id')->constrained('room_services')->cascadeOnDelete();
                $table->foreignId('cleaning_user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('status', 20)->default('DONE');
                $table->timestamp('cleaned_at');
                $table->timestamps();

                $table->unique(['room_service_id']);
                $table->index(['tenant_id', 'branch_id', 'official_shift_id', 'cleaning_user_id'], 'cleaning_tasks_shift_user_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_tasks');

        if (Schema::hasColumn('staff_profiles', 'cleaning_base_amount')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->dropColumn(['cleaning_base_amount', 'cleaning_room_amount']);
            });
        }
    }
};
