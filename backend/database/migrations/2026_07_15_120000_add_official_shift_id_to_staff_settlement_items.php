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
        Schema::table('staff_settlement_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('staff_settlement_items', 'official_shift_id')) {
                $table->unsignedBigInteger('official_shift_id')->nullable()->after('staff_settlement_id');
                $table->index('official_shift_id', 'staff_settlement_items_official_shift_idx');
                $table->foreign('official_shift_id', 'staff_settlement_items_official_shift_fk')
                    ->references('id')
                    ->on('official_shifts')
                    ->nullOnDelete();
            }
        });

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::table('staff_settlement_items')
                ->whereNull('official_shift_id')
                ->orderBy('id')
                ->get(['id', 'staff_settlement_id'])
                ->each(function ($row): void {
                    $officialShiftId = DB::table('staff_settlements')
                        ->where('id', $row->staff_settlement_id)
                        ->value('official_shift_id');

                    if ($officialShiftId !== null) {
                        DB::table('staff_settlement_items')
                            ->where('id', $row->id)
                            ->update(['official_shift_id' => $officialShiftId]);
                    }
                });
        } else {
            DB::statement(
                'UPDATE staff_settlement_items ssi '
                .'JOIN staff_settlements ss ON ss.id = ssi.staff_settlement_id '
                .'SET ssi.official_shift_id = ss.official_shift_id '
                .'WHERE ssi.official_shift_id IS NULL AND ss.official_shift_id IS NOT NULL'
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('staff_settlement_items', 'official_shift_id')) {
            return;
        }

        Schema::table('staff_settlement_items', function (Blueprint $table): void {
            $table->dropForeign('staff_settlement_items_official_shift_fk');
            $table->dropIndex('staff_settlement_items_official_shift_idx');
            $table->dropColumn('official_shift_id');
        });
    }
};
