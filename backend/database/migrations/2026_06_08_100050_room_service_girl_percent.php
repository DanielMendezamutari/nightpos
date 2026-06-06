<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->decimal('girl_percent', 5, 2)->nullable()->after('total_amount');
        });

        $defaultPercent = (float) config('nightpos.room_service.default_girl_percent', 50);

        foreach (DB::table('room_services')->orderBy('id')->get() as $row) {
            $total = (float) ($row->total_amount ?? 0);
            $girlAmount = (float) ($row->girl_amount ?? 0);

            $percent = $total > 0
                ? round($girlAmount / $total * 100, 2)
                : $defaultPercent;

            DB::table('room_services')->where('id', $row->id)->update([
                'girl_percent' => $percent,
            ]);
        }

        Schema::table('room_services', function (Blueprint $table) {
            $table->decimal('girl_percent', 5, 2)->default(50)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropColumn('girl_percent');
        });
    }
};
