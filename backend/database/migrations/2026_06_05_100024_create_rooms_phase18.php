<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('room_type', 20)->default('STANDARD');
            $table->string('status', 20)->default('AVAILABLE');
            $table->unsignedSmallInteger('default_duration_minutes')->default(60);
            $table->decimal('suggested_price', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'code']);
            $table->index(['tenant_id', 'branch_id', 'status']);
        });

        Schema::table('room_services', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('girl_user_id')->constrained('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });

        Schema::dropIfExists('rooms');
    }
};
