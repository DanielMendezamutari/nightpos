<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('shift_type', 10);
            $table->date('business_date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('OPEN');
            $table->foreignId('opened_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index(['branch_id', 'business_date']);
        });

        Schema::create('shift_closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('official_shift_id')->constrained('official_shifts')->cascadeOnDelete();
            $table->decimal('total_cash', 12, 2)->default(0);
            $table->decimal('total_qr', 12, 2)->default(0);
            $table->decimal('total_card', 12, 2)->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_manual_income', 12, 2)->default(0);
            $table->decimal('total_manual_expense', 12, 2)->default(0);
            $table->decimal('total_girl_payouts', 12, 2)->nullable();
            $table->decimal('total_waiter_payouts', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->default(0);
            $table->decimal('counted_cash', 12, 2)->default(0);
            $table->decimal('cash_difference', 12, 2)->default(0);
            $table->string('status', 20)->default('CLOSED');
            $table->foreignId('closed_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('closed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('official_shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_closures');
        Schema::dropIfExists('official_shifts');
    }
};
