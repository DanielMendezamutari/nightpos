<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('operational_events')) {
            return;
        }

        Schema::create('operational_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('type', 80);
            $table->string('target_role', 40)->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'branch_id', 'id']);
            $table->index(['tenant_id', 'branch_id', 'target_role', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_events');
    }
};
