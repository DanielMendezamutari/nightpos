<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('device_key_hash');
            $table->string('device_key_prefix', 12);
            $table->string('status', 20)->default('ACTIVE');
            $table->boolean('enabled')->default(true);
            $table->string('printer_name')->nullable();
            $table->unsignedSmallInteger('paper_width_mm')->default(80);
            $table->boolean('auto_print_order')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->text('last_error')->nullable();
            $table->string('agent_version', 40)->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'name']);
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->index('device_key_prefix');
        });

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('print_devices')->nullOnDelete();
            $table->string('type', 40);
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->string('idempotency_key', 64)->nullable();
            $table->json('payload');
            $table->text('content_text');
            $table->string('status', 20)->default('PENDING');
            $table->smallInteger('priority')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->text('last_error')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'branch_id', 'status', 'created_at']);
            $table->index(['device_id', 'status']);
            $table->unique(['tenant_id', 'branch_id', 'idempotency_key'], 'print_jobs_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
        Schema::dropIfExists('print_devices');
    }
};
