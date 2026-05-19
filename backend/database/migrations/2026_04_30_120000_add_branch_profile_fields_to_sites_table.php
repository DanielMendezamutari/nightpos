<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('legal_document_type')->nullable();
            $table->string('legal_document_number')->nullable();
            $table->string('legal_name')->nullable();
            $table->text('branch_address')->nullable();
            $table->string('branch_phone')->nullable();
            $table->string('branch_email')->nullable();
            $table->string('economic_activity')->nullable();
            $table->date('authorization_date')->nullable();
            $table->string('authorization_resolution')->nullable();
            $table->string('manager_document_type')->nullable();
            $table->string('manager_document_number')->nullable();
            $table->string('manager_full_name')->nullable();
            $table->string('currency_code', 3)->default('BOB');
            $table->unsignedInteger('ticket_series_start')->default(1);
            $table->unsignedInteger('boleta_series_start')->default(1);
            $table->unsignedInteger('factura_series_start')->default(1);
            $table->string('logo_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'legal_document_type',
                'legal_document_number',
                'legal_name',
                'branch_address',
                'branch_phone',
                'branch_email',
                'economic_activity',
                'authorization_date',
                'authorization_resolution',
                'manager_document_type',
                'manager_document_number',
                'manager_full_name',
                'currency_code',
                'ticket_series_start',
                'boleta_series_start',
                'factura_series_start',
                'logo_path',
            ]);
        });
    }
};
