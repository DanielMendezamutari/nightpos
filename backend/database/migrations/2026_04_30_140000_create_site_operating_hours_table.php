<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            /** ISO-8601: 1=Lunes … 7=Domingo (coherente con reportes y Carbon::isoWeekday()) */
            $table->unsignedTinyInteger('weekday');
            $table->boolean('is_closed')->default(false);
            /** Formato HH:MM (24h), hora local de la sucursal */
            $table->string('opens_at', 5)->nullable();
            $table->string('closes_at', 5)->nullable();
            /** Si true, el cierre cae en el calendario del día siguiente (turno noche / boliche) */
            $table->boolean('crosses_midnight')->default(false);
            $table->timestamps();

            $table->unique(['site_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_operating_hours');
    }
};
