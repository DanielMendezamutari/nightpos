<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_site_id')->nullable()->after('site_id')->constrained('sites')->nullOnDelete();
        });

        Schema::create('user_site_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'site_id']);
            $table->index(['site_id', 'user_id']);
        });

        $users = DB::table('users')->select(['id', 'site_id'])->whereNotNull('site_id')->get();
        foreach ($users as $u) {
            DB::table('user_site_accesses')->insert([
                'user_id' => $u->id,
                'site_id' => $u->site_id,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->whereNotNull('site_id')->update(['active_site_id' => DB::raw('site_id')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_site_accesses');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_site_id']);
            $table->dropColumn('active_site_id');
        });
    }
};
