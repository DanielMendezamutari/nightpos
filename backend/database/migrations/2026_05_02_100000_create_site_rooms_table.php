<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name', 120);
            /** Ambiente típico de boliche / discoteca (filtros y reportes). */
            $table->string('kind', 32)->default('other');
            $table->string('floor_label', 20)->nullable();
            $table->unsignedSmallInteger('capacity_estimate')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'code']);
        });

        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->foreignId('site_room_id')->nullable()->after('site_id')->constrained('site_rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->dropForeign(['site_room_id']);
            $table->dropColumn('site_room_id');
        });
        Schema::dropIfExists('site_rooms');
    }
};
