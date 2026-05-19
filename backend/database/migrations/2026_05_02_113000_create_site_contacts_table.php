<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('contact_type', 16); // client|companion|supplier
            $table->string('display_name', 140);
            $table->string('phone', 40)->nullable();
            $table->string('email', 140)->nullable();
            $table->string('document_type', 20)->nullable();
            $table->string('document_number', 40)->nullable();
            $table->string('business_name', 160)->nullable();
            $table->string('service_category', 80)->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['site_id', 'contact_type']);
            $table->index(['site_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contacts');
    }
};
