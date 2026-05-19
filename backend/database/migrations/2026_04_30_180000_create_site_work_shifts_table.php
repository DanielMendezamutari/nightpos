<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_work_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            /** ISO-8601: 1=Lunes … 7=Domingo */
            $table->unsignedTinyInteger('weekday');
            /** Orden del turno ese dia: 1, 2, 3… (tres turnos de 8 h, dos de 12 h, etc.) */
            $table->unsignedTinyInteger('slot_index');
            $table->string('label', 80)->nullable();
            $table->string('opens_at', 5);
            $table->string('closes_at', 5);
            /**
             * Si true, el cierre es en el calendario siguiente al inicio (medianoche o mas).
             * Permite: 22:00→05:00, 21:00→21:00 (24 h), 09:00→21:00 del dia siguiente (12 h noche), etc.
             */
            $table->boolean('crosses_midnight')->default(false);
            $table->timestamps();

            $table->unique(['site_id', 'weekday', 'slot_index']);
        });

        if (Schema::hasTable('site_operating_hours')) {
            $rows = DB::table('site_operating_hours')->get();
            foreach ($rows as $r) {
                if ($r->is_closed || $r->opens_at === null || $r->closes_at === null) {
                    continue;
                }
                DB::table('site_work_shifts')->insert([
                    'site_id' => $r->site_id,
                    'weekday' => $r->weekday,
                    'slot_index' => 1,
                    'label' => null,
                    'opens_at' => $r->opens_at,
                    'closes_at' => $r->closes_at,
                    'crosses_midnight' => (bool) $r->crosses_midnight,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            Schema::drop('site_operating_hours');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_work_shifts');

        Schema::create('site_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->boolean('is_closed')->default(false);
            $table->string('opens_at', 5)->nullable();
            $table->string('closes_at', 5)->nullable();
            $table->boolean('crosses_midnight')->default(false);
            $table->timestamps();
            $table->unique(['site_id', 'weekday']);
        });
    }
};
