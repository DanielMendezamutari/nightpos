<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saas_subscriptions', function (Blueprint $table) {
            $table->string('billing_contact_name')->nullable()->after('monthly_fee');
            $table->string('billing_contact_phone')->nullable()->after('billing_contact_name');
            $table->string('billing_contact_email')->nullable()->after('billing_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('saas_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_contact_name', 'billing_contact_phone', 'billing_contact_email']);
        });
    }
};
