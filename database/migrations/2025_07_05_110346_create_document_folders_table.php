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
        Schema::create('document_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->onDelete('cascade');
            $table->string('path')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('icon', 50)->nullable();
            $table->enum('visibility', ['private', 'internal', 'public', 'restricted'])->default('internal');
            $table->boolean('is_shared')->default(false);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'visibility']);
            $table->index(['sort_order']);
            $table->index(['path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_folders');
    }
};
