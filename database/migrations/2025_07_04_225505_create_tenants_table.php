<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database')->unique();
            $table->string('company_name');
            $table->enum('company_type', ['pharmacy', 'medical_distributor', 'clinic', 'hospital', 'other'])->default('pharmacy');
            $table->string('contact_person');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('governorate');
            $table->string('license_key')->unique();
            $table->timestamp('license_expires_at')->nullable();
            $table->integer('max_users')->default(10);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Super admin who created this tenant
            $table->timestamps();

            $table->index(['is_active', 'license_expires_at']);
            $table->index('company_type');
            $table->index('governorate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
