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
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('signature_type', ['digital', 'electronic', 'handwritten', 'biometric']);
            $table->json('signature_data')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('signed_at');
            $table->json('certificate_data')->nullable();
            $table->string('verification_code', 32)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable();
            $table->timestamps();

            $table->index(['document_id', 'user_id']);
            $table->index(['signature_type']);
            $table->index(['is_verified']);
            $table->index(['verification_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
