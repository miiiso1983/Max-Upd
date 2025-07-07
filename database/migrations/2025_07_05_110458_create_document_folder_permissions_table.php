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
        Schema::create('document_folder_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_folder_id')->constrained('document_folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('permission', ['read', 'write', 'delete', 'share']);
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique(['document_folder_id', 'user_id']);
            $table->index(['user_id', 'permission']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_folder_permissions');
    }
};
