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
            $table->decimal('girl_amount', 12, 2)->nullable()->after('total_amount');
            $table->decimal('house_amount', 12, 2)->nullable()->after('girl_amount');
        });

        DB::table('room_services')->whereNull('girl_amount')->update([
            'girl_amount' => DB::raw('total_amount'),
            'house_amount' => DB::raw('0'),
        ]);

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_duration_minutes')->nullable()->default(null)->change();
            $table->decimal('suggested_price', 12, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('room_services', function (Blueprint $table) {
            $table->dropColumn(['girl_amount', 'house_amount']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_duration_minutes')->default(60)->nullable(false)->change();
            $table->decimal('suggested_price', 12, 2)->default(0)->nullable(false)->change();
        });
    }
};
