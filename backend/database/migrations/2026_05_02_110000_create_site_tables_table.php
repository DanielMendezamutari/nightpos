<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('site_room_id')->nullable()->constrained('site_rooms')->nullOnDelete();
            $table->string('code', 32);
            $table->unsignedSmallInteger('seats')->default(4);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_tables');
    }
};
