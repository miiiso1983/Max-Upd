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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->json('value');
            $table->enum('type', ['string', 'integer', 'boolean', 'array', 'json'])->default('string');
            $table->enum('category', ['theme', 'layout', 'dashboard', 'notifications', 'language', 'general'])->default('general');
            $table->timestamps();

            // Unique constraint to prevent duplicate preferences
            $table->unique(['user_id', 'key']);

            // Indexes
            $table->index(['user_id', 'category']);
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
