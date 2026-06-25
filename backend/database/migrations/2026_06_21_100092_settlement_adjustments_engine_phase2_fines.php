<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    public function up(): void

    {

        if (! Schema::hasTable('staff_fines')) {

            Schema::create('staff_fines', function (Blueprint $table) {

                $table->id();

                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();

                $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();

                $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();

                $table->foreignId('staff_user_id')->constrained('users')->cascadeOnDelete();

                $table->string('staff_role', 30);

                $table->decimal('amount', 12, 2);

                $table->string('reason', 255);

                $table->text('notes')->nullable();

                $table->string('status', 20)->default('PENDING');

                $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();

                $table->foreignId('applied_settlement_id')->nullable()->constrained('staff_settlements')->nullOnDelete();

                $table->timestamp('applied_at')->nullable();

                $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamp('cancelled_at')->nullable();

                $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->text('cancellation_reason')->nullable();

                $table->timestamps();



                $table->index(['tenant_id', 'branch_id', 'official_shift_id', 'staff_user_id', 'status'], 'sf_scope_status_idx');

                $table->index(['cash_session_id', 'status'], 'sf_cash_status_idx');

            });

        }



        if (! Schema::hasColumn('staff_settlement_adjustments', 'staff_fine_id')) {

            Schema::table('staff_settlement_adjustments', function (Blueprint $table) {

                $table->foreignId('staff_fine_id')

                    ->nullable()

                    ->after('staff_settlement_id')

                    ->constrained('staff_fines')

                    ->nullOnDelete();

            });

        }

    }



    public function down(): void

    {

        if (Schema::hasColumn('staff_settlement_adjustments', 'staff_fine_id')) {

            Schema::table('staff_settlement_adjustments', function (Blueprint $table) {

                $table->dropConstrainedForeignId('staff_fine_id');

            });

        }



        Schema::dropIfExists('staff_fines');

    }

};


