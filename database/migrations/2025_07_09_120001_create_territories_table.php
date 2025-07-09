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
        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('governorate');
            $table->json('cities')->nullable(); // Array of cities in this territory
            $table->json('areas')->nullable(); // Array of specific areas/districts
            $table->json('postal_codes')->nullable(); // Array of postal codes
            $table->json('boundaries')->nullable(); // GPS coordinates for territory boundaries
            $table->decimal('center_latitude', 10, 8)->nullable();
            $table->decimal('center_longitude', 11, 8)->nullable();
            $table->integer('radius_km')->nullable(); // Territory radius in kilometers
            $table->enum('type', ['urban', 'rural', 'mixed'])->default('mixed');
            $table->integer('estimated_customers')->default(0);
            $table->decimal('estimated_potential', 12, 2)->default(0); // Estimated sales potential
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
            $table->json('transportation_info')->nullable(); // {preferred_transport, parking_availability, etc}
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['governorate', 'is_active']);
            $table->index(['type', 'is_active']);
            $table->index(['center_latitude', 'center_longitude']);
            $table->index('is_active');

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('territories');
    }
};
